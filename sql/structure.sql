
drop table if exists
	Test_Answer,
	Test_Choice,
	Test,
	Choice,
	Question,
	Exam,
	Student,
	Subject,
	Teacher,
	Class;

-- Class
create table Class
(
	ID			INT	primary key
					auto_increment,
	Name			CHAR(10)	unique not null,
	K			INT
) engine = innodb;

-- Teacher
create table Teacher
(
	ID			INT	primary key
					auto_increment,
	FirstName		VARCHAR(13)	not null,
	LastName		VARCHAR(30)	not null
) engine = innodb;

-- Subject
create table Subject
(
	ID			INT	primary key
					auto_increment,
	Name			VARCHAR(60)	unique not null
) engine = innodb;

-- Student
create table Student
(
	ID			INT	primary key
					auto_increment,
	Student_ID		CHAR(8)		unique,
	FirstName		VARCHAR(13)	not null,
	LastName		VARCHAR(30)	not null,
	DoB			Date		not null,
	Class			INT	references Class
					on delete cascade
					on update cascade,

	UNIQUE (FirstName, LastName, DoB, Class)
) engine = innodb;

-- Exam
create table Exam
(
	ID			INT	primary key
					auto_increment,
	Name			VARCHAR(60)	unique null,
	Class			INT	references Class
					on delete cascade
					on update cascade,
	Subject			INT	references Subject
					on delete cascade
					on update cascade,
	Time			TINYINT(1),
	Teacher			INT	references Teacher
					on delete cascade
					on update cascade,
	Duration		TIME,
	Sched_Time		DATETIME,
	Start_Time		DATETIME,
	NoQ			INT,
	Max_NoC			INT,

	UNIQUE (Class, Subject, Time)
) engine = innodb;

-- Question
create table Question
(
	ID			INT	primary key
					auto_increment,
	Text			TEXT		not null,
	Subject			INT	references Subject
					on delete cascade
					on update cascade
) engine = innodb;

-- Choice
create table Choice
(
	ID			INT	primary key
					auto_increment,
	Question		INT	references Question
					on delete cascade
					on update cascade,
	Text			TEXT		not null,
	Correct			TINYINT(1)	not null
) engine = innodb;

-- Test
create table Test
(
	ID			INT	primary key
					auto_increment,
	Student			INT	references Student
					on delete cascade
					on update cascade,
	Exam			INT	references Exam
					on delete cascade
					on update cascade,
	Time_Spent		TIME,

	UNIQUE (Student, Exam)
) engine = innodb;

-- Test_Choice
create table Test_Choice
(
	Test			INT	references Test
					on delete cascade
					on update cascade,
	Choice			INT	references Choice
					on delete cascade
					on update cascade,
	PRIMARY KEY (Test, Choice)
) engine = innodb;

-- Test_Answer
create table Test_Answer
(
	Test			INT	references Test
					on delete cascade
					on update cascade,
	Answer			INT	references Choice
					on delete cascade
					on update cascade,
	PRIMARY KEY (Test, Answer)
) engine = innodb;
