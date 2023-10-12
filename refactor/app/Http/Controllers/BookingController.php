<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $bookingRepository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Get jobs
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $response = request()->input('user_id')
            ? $this->bookingRepository->getUsersJobs($user_id)
            : (
                $request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') || $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID')
                    ? $this->bookingRepository->getAll($request) : []
            );
        return response($response);
    }

    /**
     * get a single job details
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->bookingRepository->with('translatorJobRel.user')->findorFail($id);
        return response($job);
    }

    /**
     * Insert a new record
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $response = $this->bookingRepository->store($request->__authenticatedUser, $request->validated());
        return response($response);
    }

    /**
     * updating a record
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->except(['_token', 'submit']);
        $response = $this->bookingRepository->updateJob($id, $data, $request->__authenticatedUser);
        return response($response);
    }

    /**
     * updating information of job
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $response = $this->bookingRepository->storeJobEmail($request->all());
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        $response = request()->input('user_id')
            ? $this->bookingRepository->getUsersJobsHistory(request()->user_id, $request)
            : [];
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        return response($this->bookingRepository->acceptJob($request->all(), $request->__authenticatedUser));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function acceptJobWithId(Request $request)
    {
        return response($this->bookingRepository->acceptJobWithId(request()->only('job_id'), $request->__authenticatedUser));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function cancelJob(Request $request)
    {
        return response($this->bookingRepository->cancelJobAjax($request->all(), $request->__authenticatedUser));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function endJob(Request $request)
    {
        return response($this->bookingRepository->endJob($request->all()));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function customerNotCall(Request $request)
    {
        return response($this->bookingRepository->customerNotCall($request->all()));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getPotentialJobs(Request $request)
    {
        return response($this->bookingRepository->getPotentialJobs($request->__authenticatedUser));
    }

    /**
     * Updating distance
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function distanceFeed(Request $request)
    {
        try {
            // this part can be done in a saperate custom request where we will create a separate request for validating the data and it will be called before diving into the function
            $request->validate([
                'job_id' => 'required|integer', // assuming it will be an integer
                'admincomment' => Rule::requiredIf(fn () => request()->input('flagged') && request()->flagged == 'true') // assuming that flagged is a string value not boolean
            ]);

            // inserting or overriding values
            $request->merge([
                'distance' => request()->input('distance') ? request()->distance : '',
                'time' => request()->input('time') ? request()->time : '',
                'session_time' => request()->input('session_time') ? request()->session_time : '',
                'flagged' => request()->input('flagged') && request()->flagged == 'true'
                    ? 'yes' : 'no',
                'manually_handled' => request()->input('manually_handled') && request()->manually_handled == 'true' ? 'yes' : 'no',
                'by_admin' => request()->input('by_admin') && request()->by_admin == 'true'
                    ? 'yes' : 'no',
                'admincomment' => request()->input('admincomment') ? request()->admincomment : '',
            ]);

            // updating the distance based on time and distance value
            if (request()->input('time') || request()->input('distance')) {
                // finding distance based on job id
                $distance = Distance::where('job_id', request()->job_id)->first();
                if ($distance)
                $distance->update([
                    'distance' => request()->distance,
                    'time' => request()->time
                ]);
            }

            // updating job information based on a condition
            if (in_array('yes', [request()->flagged, request()->manually_handled, request()->by_admin]) || request()->input('session_time')) {
                $job = Job::find(request()->job_id);
                // The optional helper function is used to safely access the object without causing errors if it is null.
                // If object is null, the fill method will not be called.
                optional($job)->fill(request()->only('admin_comments', 'flagged', 'session_time', 'manually_handled', 'by_admin'))->save();
            }

            return response('Record updated!');
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->bookingRepository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->bookingRepository->find($data['jobid']);
        $job_data = $this->bookingRepository->jobToData($job);
        $this->bookingRepository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->bookingRepository->find($data['jobid']);
        $job_data = $this->bookingRepository->jobToData($job);

        try {
            $this->bookingRepository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
