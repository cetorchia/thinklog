#
# The following SQL is for creating the tables of Thinklog
# (c) 2010,2012 Carlos E. Torchia
# Licensed under GPLv2
#

create table if not exists thinkers (
	thinker_id varchar(64) primary key,
	password varchar(40),
	about varchar(512),
	name varchar(64)
);

create table if not exists thoughts (
	thought_id integer primary key auto_increment,
	content varchar(2048),
	thinker_id varchar(64),
	date timestamp default current_timestamp,
	private tinyint
);

create table if not exists keywords (
	keyword_id integer primary key auto_increment,
	keyword varchar(128)
);

create table if not exists common_keywords (
	keyword_id integer primary key
);

create table if not exists mentions (
	thought_id integer,
	keyword_id integer
);

create table if not exists related_keywords (
	keyword1 integer,
	keyword2 integer
);

create table if not exists pair_counts (
	keyword1 integer,
	keyword2 integer,
	count integer
);

