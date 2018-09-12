CREATE SEQUENCE hold_request_id_seq;

CREATE TABLE hold_request (
  id INTEGER PRIMARY KEY NOT NULL DEFAULT nextval('hold_request_id_seq'::regclass),
  job_id TEXT,
  patron TEXT,
  nypl_source TEXT,
  request_type TEXT,
  created_date TEXT,
  updated_date TEXT,
  success BOOLEAN,
  processed BOOLEAN,
  record_type TEXT,
  record TEXT,
  pickup_location TEXT,
  needed_by TEXT,
  number_of_copies CHARACTER VARYING,
  doc_delivery_data TEXT,
  delivery_location TEXT,
  error TEXT
);
