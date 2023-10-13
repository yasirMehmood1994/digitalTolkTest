<!-- Please go through the notes -->
Note:
    * I have modified all the code of the controller
    * Some of the code from to repository is updated as well i.e.
        1. All the code in the repository related to index method of the controller is refactored
        2. Some part of the code in the repository related to store method is also refactored
    * Couldn't able to all the code due to lack of time but from the code you can get all your possible answers like coding style, mentality etc
<!-- Amaizing part -->
Amazing part of the code is that its using repository to maintain the structure of the code so that it should be more readable and easy to understand and an advance way of doing it rather then writing all the logics in the controller

<!-- Ok part -->
There are less comments on the code which makes certain complexities while having an understanding of the code more the comments on the code more we can know what functionality is being performed

<!-- Worst part -->
Repository code is completely messed up
* No comments any new person will have to put a great effort to have the understanding of code
* Variables naming conventions are not proper which also is a worst part of the code
* There are so many conditions makes code more complex
* Repository contains 2000+ lines of code which also turns it into worst category

========================================================================================================
<!-- If i were doing it -->

Let me share my thoughts file wise
1. BookingController
    Code in that file seems much better but yes some of the parts we can improve them like
    * Rather than using long form of code we can use the shorter version to use the conditions like
    ? : rather then if () {} else {}
    * Some the functions contains so many if and else in my first approach is rather than using if else I would go with the form validation rather then putting condition and validating data and if there is a scenario where I need to use too many if else, I would prefer switch instead easier to manage the code.
    * Some of the variables are created but never being used so I'll avoid such practices
    * Try catch missing to handle the exceptions if any
    * Related to queries there are certain modifications which needs to be done which I'll be doing in the code refactor part
2. BookingRepository
    Code in this file seems to much messy number of lines are 2000+ which is an issue for a developer to modify it, so my methodology of doing it will be
    * Keep the limited code here do not put each and everything in the repository there is proper way of doing it to create traits, helper functions, services as per need and call them here and use it rather than putting all the code in a single file
    * Filtering out as much as possible data though middleware’s rather than putting custom conditions in it
    * Using validations and rules to validate the data rather than putting if else in the code before insertion just get the clean data and operate your query here
    * Again, for the restriction of actions I'll prefer middleware model policy and maintain the proper structure of code
    * I would preferably use migrations to set the default values instead of putting if and buts in the code to set the default values before any crate / update action there are many other methods to do it so like doing it using event / listeners but to me setting default values via migration is best way to do until I got some sort of limitation
    * If I need to perform some sort of actions whenever an action is performed, I would prefer Observer / Eloquent ORM, we can define boot methods in our model that are attached that triggers the action and you can make changes there if needed.
    * I have seen some custom quires as well, I'll never go with custom quires until face some deadlock while performing task Laravel query builder and eloquent relationship provide us to use the queries and joins in a very good and advance way so I'll definitely prefer that