CREATE TABLE IF NOT EXISTS users (
  first_name text,
  last_name text,
  settlement_id integer,
  user_id integer unique
);

CREATE TABLE IF NOT EXISTS wall_get (
  attachments text,
  comments_are_committed integer,
  date integer,
  from_id integer,
  owner_id integer,
  post_id integer,
  settlement_id integer,
  text text,
  unique(owner_id,post_id)
);

CREATE TABLE IF NOT EXISTS wall_getcomments (
  attachments text,
  comment_id integer,
  date integer,
  from_id integer,
  owner_id integer,
  parent_comment_id integer,
  post_id integer,
  settlement_id integer,
  text text,
  unique(owner_id,post_id,parent_comment_id,comment_id)
);
