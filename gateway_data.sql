-- Copyright (c) 2008-2012 BlueWall Information Technologies, LLC
--
--   Licensed under the Apache License, Version 2.0 (the "License");
--   you may not use this file except in compliance with the License.
--   You may obtain a copy of the License at
--
--       http://www.apache.org/licenses/LICENSE-2.0
--
--   Unless required by applicable law or agreed to in writing, software
--   distributed under the License is distributed on an "AS IS" BASIS,
--   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
--   See the License for the specific language governing permissions and
--   limitations under the License.
--
--
-- Table structure for table `channels` using MySQL
-- Using PDO See: http://php.net/manual/en/book.pdo.php
--

DROP TABLE IF EXISTS channels;

CREATE TABLE `channels` (
  `channel` varchar(36) NOT NULL,
  `uri` varchar(60) NOT NULL,
  `item` varchar(36) NOT NULL,
  PRIMARY KEY (`item`),
  UNIQUE KEY `item` (`item`,`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Active lsl xmlrpc channels on the grid';
