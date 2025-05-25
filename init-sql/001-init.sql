create table users(
	id int AUTO_INCREMENT primary key,
	email varchar(255) unique not null,
	password varchar(255) not null,
	surname varchar(255) not null,
	first_name varchar(255) not null,
	created_at timestamp default current_timestamp
);

create table todos(
	id int auto_increment primary key,
	user_id int not null,
	title varchar(255) not null,
	is_done boolean default false,
	created_at timestamp default current_timestamp,
	foreign key (user_id) references users(id) on delete cascade
);