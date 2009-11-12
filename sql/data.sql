
set names utf8;

insert into User values ("admin", sha1("admin"));

insert into Class values (null, "CN1K6E", 6);
insert into Class values (null, "CN1K7D", 7);
insert into Class values (null, "CN1K8G", 8);

insert into Teacher values (null, "Minh", "Hồ Chí");
insert into Teacher values (null, "Hữu", "Tố");
insert into Teacher values (null, "Giót", "Phan Đình");
insert into Teacher values (null, "Kích", "O Du");

insert into Subject values (null, "Xử lý ảnh");
insert into Subject values (null, "Trí tuệ nhân tạo");
insert into Subject values (null, "Cấu trúc dữ liệu và giải thuật");

insert into Student values (null, "0", "0", "0", "1945-09-02", 1);
