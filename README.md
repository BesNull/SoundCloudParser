# SoundCloudParser
Таблицы:

CREATE TABLE `media_artists` (
  `Username` varchar(50) NOT NULL,
  `Fullname` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `media_artists`
  ADD PRIMARY KEY (`Username`);
COMMIT;
