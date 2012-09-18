<?php

// web host
define("THINKLOG_URL", "http://localhost/carlos/thinklog/");

//
// Directory definitions
//

define("DOC_ROOT", "/home/carlos/public_html/thinklog/");

//
// Database definitions
//

define("MYSQL_DB", "thinklog");
define("MYSQL_HOST", "localhost");
define("MYSQL_USER", "www-data");
define("MYSQL_PASSWORD", "ablative");

//
// Keyword behaviour defintions
//

define("KEYWORD_THRESHOLD", 5);
define("HASH_TAG_REGEX", '/(?:^|\s)\#([_\w]+)/');

//
// User interface definitions
//

define("DEFAULT_QUERY_RESULTS_PER_PAGE", 10);
define("MAX_BODY_LENGTH", 2048);
