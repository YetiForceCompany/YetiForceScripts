# YetiForce RestApi SDK

```
apiPath =  __CRM_URL__/webservice/RestApi/   
```
## Configuration file

```php
return [
	'apiPath' => 'https://gitdeveloper.yetiforce.com/webservice/RestApi/',
	'wsAppName' => 'portal',
	'wsAppPass' => 'portal',
	'wsApiKey' => 'VMUwRByXHSq1bLW485ikfvcC97P6gJsz',
	'wsUserName' => 'demo@yetiforce.com',
	'wsUserPass' => 'demo',
	'bruteForceDriver' => 'db',
	'bruteForceDayLimit' => 1000,
	'logDriver' => 'db',
	'dbHost' => 'localhost',
	'dbName' => 'api',
	'dbPort' => 3306,
	'dbUser' => 'api',
	'dbPass' => '',
];
```

## Database structure [optional]

bruteforce table [optional]

```sql
CREATE TABLE `bruteforce` (
`ip` varchar(40) NOT NULL,
`counter` smallint(5) unsigned NOT NULL DEFAULT 1,
`last_request` datetime NOT NULL,
KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

```

errors table [optional]

```sql
CREATE TABLE `errors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `type` varchar(20) NOT NULL,
  `code` smallint(4) unsigned NOT NULL,
  `message` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `params` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
```

logs table [optional]

```sql
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `request_time` decimal(10,2) unsigned NOT NULL,
  `code` smallint(4) unsigned NOT NULL,
  `reason_phrase` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `params` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
```
