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

CREATE TABLE IF NOT EXISTS wall_get_photos (
  access_key text,
  comments_are_committed integer,
  date integer,
  owner_id integer,
  photo_id integer,
  photo_owner_id integer,
  post_id integer,
  unique(owner_id,post_id,photo_owner_id,photo_id)
);

CREATE TABLE IF NOT EXISTS wall_get_photos_comments (
  access_key text,
  attachments text,
  comment_id integer,
  date integer,
  from_id integer,
  owner_id integer,
  photo_id integer,
  photo_owner_id integer,
  post_id integer,
  settlement_id integer,
  text text,
  unique(owner_id,post_id,photo_owner_id,photo_id,comment_id)
);

CREATE TABLE IF NOT EXISTS wall_get_videos (
  access_key text,
  comments_are_committed integer,
  date integer,
  owner_id integer,
  video_id integer,
  video_owner_id integer,
  post_id integer,
  unique(owner_id,post_id,video_owner_id,video_id)
);

CREATE TABLE IF NOT EXISTS wall_get_videos_comments (
  access_key text,
  attachments text,
  comment_id integer,
  date integer,
  from_id integer,
  owner_id integer,
  video_id integer,
  video_owner_id integer,
  post_id integer,
  settlement_id integer,
  text text,
  unique(owner_id,post_id,video_owner_id,video_id,comment_id)
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
