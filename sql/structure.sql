
drop table if exists oes_User;
create table oes_User
(
	ID		CHAR(13)	primary key,
	Pass		CHAR(40)	not null
)
collate utf8_unicode_ci
engine = innodb;

drop table if exists
	oes_Answer,
	oes_TQ,
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
	ID		INT		primary key	auto_increment,
	Name		CHAR(10)	unique not null,
	K		INT
)
collate utf8_unicode_ci
engine = innodb;

-- Teacher
create table oes_Teacher
(
	ID		INT		primary key	auto_increment,
	FirstName	VARCHAR(13)	not null,
	LastName	VARCHAR(30)	not null
)
collate utf8_unicode_ci
engine = innodb;

-- Subject
create table oes_Subject
(
	ID		INT		primary key	auto_increment,
	Name		VARCHAR(60)	unique not null
)
collate utf8_unicode_ci
engine = innodb;

-- Student
create table oes_Student
(
	ID		INT		primary key	auto_increment,
	IDCode		CHAR(8)		unique,
	FirstName	VARCHAR(13)	not null,
	LastName	VARCHAR(30)	not null,
	DoB		DATE		not null,
	Class		INT,

	UNIQUE (FirstName, LastName, DoB, Class),
	FOREIGN KEY (Class)	REFERENCES oes_Class(ID)
)
collate utf8_unicode_ci
engine = innodb;

-- Exam
create table oes_Exam
(
	ID		INT		primary key	auto_increment,
	Name		VARCHAR(60)	unique null,
	Class		INT,
	Subject		INT,
	Time		TINYINT(1),
	Teacher		INT,
	Duration	INT,
	NoQ		INT,
	Schedule	DATETIME,
	StartTime	DATETIME,
	EndTime		DATETIME,

	UNIQUE (Class, Subject, Time),
	FOREIGN KEY (Class)	REFERENCES oes_Class(ID),
	FOREIGN KEY (Subject)	REFERENCES oes_Subject(ID),
	FOREIGN KEY (Teacher)	REFERENCES oes_Teacher(ID)
)
collate utf8_unicode_ci
engine = innodb;

-- Question
create table oes_Question
(
	ID		INT		primary key	auto_increment,
	Text		TEXT		not null,
	Subject		INT,
	Shuffleable	TINYINT(1)	not null,
	Rank		FLOAT,

	FOREIGN KEY (Subject)	REFERENCES oes_Subject(ID)
)
collate utf8_unicode_ci
engine = innodb;

-- Choice
create table oes_Choice
(
	ID		INT		primary key	auto_increment,
	Question	INT,
	Text		TEXT		not null,
	Correct		TINYINT(1)	not null,
	Exclusive	TINYINT(1)	not null,

	FOREIGN KEY (Question)	REFERENCES oes_Question(ID)
)
collate utf8_unicode_ci
engine = innodb;

-- Test
create table oes_Test
(
	ID		INT		primary key	auto_increment,
	Student		INT,
	Exam		INT,
	TimeSpent	INT,

	UNIQUE (Student, Exam),
	FOREIGN KEY (Student)	REFERENCES oes_Student(ID),
	FOREIGN KEY (Exam)	REFERENCES oes_Exam(ID)
)
collate utf8_unicode_ci
engine = innodb;

-- TestQuestion
create table oes_TQ
(
	ID		INT		primary key	auto_increment,
	Test		INT,
	Question	INT,

	UNIQUE (Test, Question),
	FOREIGN KEY (Test)	REFERENCES oes_Test(ID),
	FOREIGN KEY (Question)	REFERENCES oes_Question(ID)
)
collate utf8_unicode_ci
engine = innodb;

-- Answer
create table oes_Answer
(
	TQ		INT,
	Choice		INT,

	PRIMARY KEY (TQ, Choice),
	FOREIGN KEY (TQ)	REFERENCES oes_TQ(ID),
	FOREIGN KEY (Choice)	REFERENCES oes_Choice(ID)
)
collate utf8_unicode_ci
engine = innodb;
