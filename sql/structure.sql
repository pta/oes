
drop table if exists Exam;
drop table if exists Student;
drop table if exists Subject;
drop table if exists Teacher;
drop table if exists Class;

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

