
set names utf8;

insert into User values ("admin", sha1("admin"));

insert into Class values (null, "CN1K6E", 6);
insert into Class values (null, "CN1K7D", 7);
insert into Class values (null, "CN1K8G", 8);
insert into Class values (null, "CN1K6G", 6);

insert into Teacher values (null, "Phèo", "Nguyễn Chí");
insert into Teacher values (null, "Nở", "Phạm Thị");

insert into Subject values (null, "Xử lý ảnh");
insert into Subject values (null, "Trí tuệ nhân tạo");
insert into Subject values (null, "Cấu trúc dữ liệu và giải thuật");

insert into Exam values (null, "ABC", 1, 1, 1, 1, 60,
	'2009-11-11 15:00:00', '2009-11-11 15:00:00', null, 26, 5, 1);

insert into Student values (null, "0", "0", "0", "1945-09-02", 1);
