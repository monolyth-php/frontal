# Keeping your database up to date
The [dbMover](http://dbmover.monomelodies.nl) package provides a way to automate
your database migrations. No more stupid migration scripts to write! (Well, more
or less depending on how complex the migration is.)

Their documentation explains how it works, so check it out. You might consider
adding the procedure to your workflow, e.g. by running it from a Git
`post-update` hook.

The essential idea is that programmers just write and update a _schema file_
which defines how the database should be setup when initially run against an
empty, virgin database. dbMover then figures out what still needs to be done,
and what parts are already okay.

> dbMover currently works on MySQL and PostgreSQL, with other vendors to be
> added. As you can deduce from the 0.x version number, it is still slightly
> experimental - although we're already using it against production databases.
> Still, you should take heed of the warning in the manual: backup your data if
> you want to be _sure_ you don't lose anything.

More information: [dbMover documentation](http://dbmover.monomelodies.nl)

