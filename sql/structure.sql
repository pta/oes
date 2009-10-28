
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
	ID			INT	PRIMARY KEY
					AUTO_INCREMENT,
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
	Name			VARCHAR(20)
);

-- Student
create table Student
(
	ID			INT	primary key
					auto_increment,
	Student_ID		CHAR(8),
	FirstName		VARCHAR(13),
	LastName		VARCHAR(30),
	DoB			Date,
	Class			INT	references Class
					on delete cascade
					on update cascade
);

-- Exam
create table Exam
(
	ID			INT	primary key
					auto_increment,
	Name			VARCHAR(60),
	Class			INT	references Class
					on delete cascade
					on update cascade,
	Subject			INT	references Subject
					on delete cascade
					on update cascade,
	Teacher			INT	references Teacher
					on delete cascade
					on update cascade,
	Duration		TIME,
	Sched_Time		DATETIME,
	Start_Time		DATETIME
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
					on update cascade
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
