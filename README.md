# SoundCloudParser
Таблицы:

CREATE TABLE `media_artists` (

  `Username` varchar(50) NOT NULL PRIMARY KEY,
  
  `Fullname` varchar(50) NOT NULL
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

<br/>
<br/>


CREATE TABLE `media_tracks` (

  `track_id` int(11) NOT NULL AUTO_INCREMENT UNIQUE,
  
  `username` varchar(50) NOT NULL,
  
  `track_name` varchar(50) NOT NULL,
  
  `duration` int(11) NOT NULL,
  
  `description` varchar(100) DEFAULT NULL,
  
  PRIMARY KEY(`username`, `track_name`),
  
  FOREIGN KEY (`username`) REFERENCES media_artists(`Username`)
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

