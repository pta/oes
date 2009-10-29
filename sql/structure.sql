
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
	Name			CHAR(10),
	K			INT
);

-- Teacher
create table Teacher
(
	ID			INT	primary key
					auto_increment,
	FirstName		VARCHAR(13),
	LastName		VARCHAR(30)
);

-- Subject
create table Subject
(
	ID			INT	primary key
					auto_increment,
	Name			VARCHAR(60)	unique
);

-- Student
create table Student
(
	ID			INT	primary key
					auto_increment,
	Student_ID		CHAR(8)		unique null,
	FirstName		VARCHAR(13),
	LastName		VARCHAR(30),
	DoB			Date,
	Class			INT	references Class
					on delete cascade
					on update cascade,

	UNIQUE (FirstName, LastName, DoB, Class)
);

-- Exam
create table Exam
(
	ID			INT	primary key
					auto_increment,
	Name			VARCHAR(60)	unique,
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

	UNIQUE (Class, Subject, Time)
);

-- Question
create table Question
(
	ID			INT	primary key
					auto_increment,
	Text			TEXT,
	Subject			INT	references Subject
					on delete cascade
					on update cascade
);

-- Choice
create table Choice
(
	ID			INT	primary key
					auto_increment,
	Question		INT	references Question
					on delete cascade
					on update cascade,
	Text			TEXT,
	Correct			TINYINT(1)
);

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

	UNIQUE (Student, Exam)
);

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
);

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
);
