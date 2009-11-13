
drop table if exists oes_User;
create table oes_User
(
	ID			CHAR(13)	primary key,
	Pass			CHAR(40)	not null
)
	collate utf8_unicode_ci
	engine = innodb;

drop table if exists
	oes_Test_Answer,
	oes_Test_Choice,
	oes_Test,
	oes_Choice,
	oes_Question,
	oes_Exam,
	oes_Student,
	oes_Subject,
	oes_Teacher,
	oes_Class;

-- Class
create table oes_Class
(
	ID			INT	primary key
					auto_increment,
	Name			CHAR(10)	unique not null,
	K			INT
)
	collate utf8_unicode_ci
	engine = innodb;

-- Teacher
create table oes_Teacher
(
	ID			INT	primary key
					auto_increment,
	FirstName		VARCHAR(13)	not null,
	LastName		VARCHAR(30)	not null
)
	collate utf8_unicode_ci
	engine = innodb;

-- Subject
create table oes_Subject
(
	ID			INT	primary key
					auto_increment,
	Name			VARCHAR(60)	unique not null
)
	collate utf8_unicode_ci
	engine = innodb;

-- Student
create table oes_Student
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
)
	collate utf8_unicode_ci
	engine = innodb;

-- Exam
create table oes_Exam
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
	Duration		INT,
	Sched_Time		DATETIME,
	Start_Time		DATETIME,
	End_Time		DATETIME,
	NoQ			INT,
	Max_NoC			INT,
	Mul_Choice		INT,

	UNIQUE (Class, Subject, Time)
)
	collate utf8_unicode_ci
	engine = innodb;

-- Question
create table oes_Question
(
	ID			INT	primary key
					auto_increment,
	Text			TEXT		not null,
	Subject			INT	references Subject
					on delete cascade
					on update cascade
)
	collate utf8_unicode_ci
	engine = innodb;

-- Choice
create table oes_Choice
(
	ID			INT	primary key
					auto_increment,
	Question		INT	references Question
					on delete cascade
					on update cascade,
	Text			TEXT		not null,
	Correct			TINYINT(1)	not null
)
	collate utf8_unicode_ci
	engine = innodb;

-- Test
create table oes_Test
(
	ID			INT	primary key
					auto_increment,
	Student			INT	references Student
					on delete cascade
					on update cascade,
	Exam			INT	references Exam
					on delete cascade
					on update cascade,
	Time_Spent		INT,

	UNIQUE (Student, Exam)
)
	collate utf8_unicode_ci
	engine = innodb;

-- Test_Choice
create table oes_Test_Choice
(
	Ord			INT,
	Test			INT	references Test
					on delete cascade
					on update cascade,
	Choice			INT	references Choice
					on delete cascade
					on update cascade,
	PRIMARY KEY (Test, Choice)
)
	collate utf8_unicode_ci
	engine = innodb;

-- Test_Answer
create table oes_Test_Answer
(
	Test			INT	references Test
					on delete cascade
					on update cascade,
	Answer			INT	references Choice
					on delete cascade
					on update cascade,
	PRIMARY KEY (Test, Answer)
)
	collate utf8_unicode_ci
	engine = innodb;
