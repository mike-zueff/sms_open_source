CREATE TABLE wall_get (
  city_id integer,
  date integer,
  from_id integer,
  owner_id integer,
  post_id integer,
  text text
);

CREATE TABLE wall_get_comments (
  city_id integer,
  comment_id integer,
  date integer,
  from_id integer,
  owner_id integer,
  parent_comment_id integer,
  post_id integer,
  text text
);
