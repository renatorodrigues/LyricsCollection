<?php
require_once('config.php');

$sql = <<<'EOD'
DROP TABLE IF EXISTS Songs, Artists, Lyrics;

CREATE TABLE Artists (
  mb_id VARCHAR(36) PRIMARY KEY,
  name VARCHAR(255)
) ENGINE INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Lyrics (
  id BIGINT UNSIGNED PRIMARY KEY,
  checksum VARCHAR(32),
  content TEXT,
  url VARCHAR(255)
) ENGINE INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE Songs (
  mb_id VARCHAR(36) PRIMARY KEY,
  title VARCHAR(255),
  artist_id VARCHAR(36),
  lyric_id BIGINT UNSIGNED,
  checked BIT DEFAULT 0,
  FOREIGN KEY (artist_id) REFERENCES Artists(mb_id) ON DELETE CASCADE,
  FOREIGN KEY (lyric_id) REFERENCES Lyrics(id) ON DELETE SET NULL
) ENGINE INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;
EOD;

try {
  $pdo->exec($sql);
} catch (PDOException $e) {
  echo 'An error ocurred while creating database: ';
  die($e->getMessage());
}

echo 'Database successfuly created!';
?>