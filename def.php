<?php

// web host
define("THINKLOG_URL", "http://localhost/carlos/thinklog/");

//
// Directory definitions
//

define("DOC_ROOT", "/home/ctorchia/public_html/thinklog/");

//
// Database definitions
//

define("MYSQL_DB", "thinklog");
define("MYSQL_HOST", "localhost");
define("MYSQL_USER", "www-data");
define("MYSQL_PASSWORD", "ablative");

//
// Triple store definitions
//

define("THINKLOG_GRAPH", THINKLOG_URL);
define("STORE_NAME","thinklog");
define("SPARQL_PREFIXES",
	"PREFIX owl: <http://www.w3.org/2002/07/owl#>. " .
	"PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>. " .
	"PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>. " .
	"PREFIX thinklog: <" . THINKLOG_URL . "#>. " .
"");

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
