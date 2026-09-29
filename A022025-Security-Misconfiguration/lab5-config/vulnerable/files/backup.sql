-- VULNERABLE: full DB backup left in a browsable web directory (CWE-16, CWE-538)
-- In production this file would contain password hashes and PII.
--)table users(id, username, password_sha1, role);
insert into users values(1,'admin','d033e22ae348aeb5660fc2140aec35850c4da997','admin');
insert into users values(2,'support','5baa61e4c9b93f3f0682250b6cf8331b7ee68a','user');
-- crack station: admin / 5baa61e4... = well-known hashes. Rotate + re-hash with bcrypt.
