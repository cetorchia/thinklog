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
	keyword varchar(128),
	UNIQUE (keyword)
);

create table if not exists mentions (
	thought_id integer,
	keyword_id integer,
	UNIQUE (thought_id, keyword_id)
);

create or replace view keyword_count
as select keyword_id, count(thought_id) as cnt
   from mentions
   group by keyword_id
;

create or replace view common_keywords
as select keyword_id
   from keyword_count
   where cnt > 5
;

create or replace view keyword_pair_count
as select m1.keyword_id as keyword1, m2.keyword_id as keyword2,
          count(m1.thought_id) as cnt
   from mentions m1, mentions m2
   where m1.thought_id = m2.thought_id
     and m1.keyword_id <> m2.keyword_id
   group by m1.keyword_id, m2.keyword_id;

create or replace view related_keywords
as select keyword1, keyword2
   from keyword_pair_count
   where cnt > 5
;
