# Threads

Threads would be the categories in which your (users') topics would be stored, you store your threads by location.

Some parts of your site (e.g. blogs) don't need multiple threads, only one would be needed with location set to 'blogs',
any topics created in here would relate to a blog's post, to make it easy to find topics you would set the title of the topic to the blog post's id (all replies would be treated as comments to that post).

Whereas a section of your site like a forum would require multiple categories (all of these would have an identical location, e.g. 'forum')
where users are free to create their own topics and, in turn, reply to.

## Creating a thread

You create a thread by calling an ORM instance loaded with ```Quill_Thread```

	$thread = ORM::factory('Quill_Thread');

There are only 2 fields required to get your thread up:

 - location: this is how we'll be retrieving the thread
 - status (open or closed), when closed no new topics can be added

Optionally you could also define:

 - title
 - description

		$thread->values(array(
			'location' => 'forum',
			'status' => 'open',
			'title' => 'Anouncements',
			'description' => 'Site-related updates and anouncements'
		));
		$thread->save();

### Topic-related options

You can define how much of the bundled functionality you want to use for topics on a per-thread-basis:

 - stickies: will this thread contain stickies?
 - count_replies: Should the topics keep track of the amount of replies they get?
 - record_last_post: Should we store the user that made the last reply to a topic?

All of these options default to false, to keep everything light, declare these columns to be ```TRUE``` in your model when needed.

## Retrieving a thread

There are several ways you can retrieve a thread, but it's always done through [Quill::factory()].

		// Retrieve a thread with 1 as its id
		$thread = Quill::factory(1);

		// Retrieve the (first) thread for location 'forum'
		$thread = Quill::factory('forum');

		// Retrieve a thread by instance
		$source = ORM::factory('Quill_Thread', array('title' => 'Anouncements'));
		$thread = Quill::factory($source);

This will return a [Quill] instance that'll help you create and load discussions (topics and replies).

## Retrieving all threads by location

If you have a location that supposedly has multiple threads you will have to call them through [Quill::threads()]

It takes 2 arguments, the first one is the location in which the threads are stored, the second on is which status they would need to have.

		// Retrieve all threads that have their status as 'open' in 'forum'
		$threads = Quill::threads('forum');
		$threads = Quill::threads('forum', 'open'); // in this case the second argument can be omitted

		// Retrieve all threads that have their status as 'closed' in 'forum'
		$threads = Quill::threads('forum', 'closed');

		// Retrieve all threads  in 'forum'
		$threads = Quill::threads('forum', false);
