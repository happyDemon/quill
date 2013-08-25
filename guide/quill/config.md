# Config

**time_format**: The [format](http://php.net/manual/en/function.date.php) in which date/time is saved to the DB and displayed to your users

**auto_create_thread**: Should threads be created automatically when you call them through ```Quill::factory()```?

## default_options

These options are used as defaults when creating new threads, so you won't have to define them everytime when you create one.
After a thread is created you can still change these options in the database table.

**count_replies**: Should the topics in this thread keep track of the amount of replies they have?

**stickies**: Should topics in this category be ordered with stickied topics first?

**record_last_post**: Should the user that replied last to a topic be stored and be retrievable when loading topics in this thread?