
\xampplite\mysql\bin\mysql -u root -e"source sql/clean.sql"

\xampplite\mysql\bin\mysql -u root -e"source sql/database.sql"

\xampplite\mysql\bin\mysql -u oes -p123456 -D oes -e"source sql/structure.sql"

\xampplite\mysql\bin\mysql -u oes -p123456 -D oes -e"source sql/data.sql"

@PAUSE
