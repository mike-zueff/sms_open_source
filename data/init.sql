CREATE TABLE wall_get (
  attachments text,
  date integer,
  from_id integer,
  owner_id integer,
  post_id integer,
  settlement_id integer,
  text text
);

CREATE TABLE wall_getcomments (
  attachments text,
  comment_id integer,
  date integer,
  from_id integer,
  owner_id integer,
  parent_comment_id integer,
  post_id integer,
  settlement_id integer,
  text text
);
