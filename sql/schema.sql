#
# The following SQL is for creating the tables of Thinklog
# (c) 2010 Carlos E. Torchia
# Licensed under GPLv2
#

create table if not exists thinkers (
	thinker_id varchar(64) primary key,
	password varchar(40)
);
