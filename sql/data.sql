
set names utf8;

insert into oes_User values ("admin", sha1("admin"));

insert into oes_Class values (null, "CN1K6E", 6);
insert into oes_Class values (null, "CN1K7D", 7);
insert into oes_Class values (null, "CN1K8G", 8);

insert into oes_Teacher values (null, "Minh", "Hồ Chí");
insert into oes_Teacher values (null, "Hữu", "Tố");
insert into oes_Teacher values (null, "Giót", "Phan Đình");
insert into oes_Teacher values (null, "Kích", "O Du");

/*
insert into oes_Subject values (null, "Xử lý ảnh");
insert into oes_Subject values (null, "Trí tuệ nhân tạo");
insert into oes_Subject values (null, "Cấu trúc dữ liệu và giải thuật");
*/

insert into oes_Student values (null, "0", "0", "0", "1945-09-02", 1);
