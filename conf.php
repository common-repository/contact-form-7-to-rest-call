<?php
class Conf
{
 const cf7RESThost='mydoimain.com'; 
 const cf7RESTport='90';
 const cf7RESTmethod="POST";
 const cf7RESTpath = 'http://mydoimain.com/api/something';
 
 /*BODY  {tag-names-to-auto-replace}*/
 public static function GeBody()
 {
 $cf7RESTparams= 
<<<BODY
						<?xml version="1.0" encoding="UTF-8"?>
												<data>
												  <lead>
													<key></key>
													<leadgroup></leadgroup>
													<firstname></firstname>
													<lastname></lastname>  
													<site></site>
													<introducer></introducer>
													<type></type>
													<user></user>
													<status></status>
													<reference></reference>
													<source></source>
													<medium></medium>
													<term></term>
													<cost></cost>
													<value></value>
													<title></title>
													<company></company>
													<jobtitle></jobtitle>
													<phone1></phone1>
													<phone2></phone2>
													<fax></fax>
													<email></email>
													<address></address>
													<address2></address2>
													<address3></address3>
													<towncity></towncity>
													<postcode></postcode>
													<dobday></dobday>
													<dobmonth></dobmonth>
													<dobyear></dobyear>
													<contactphone></contactphone>
													<contactfax></contactfax>
													<contactemail></contactemail>
													<contactmail></contactmail>
													<contacttime></contacttime>
													<data1></data1>
												  </lead>
												</data>
BODY;

return $cf7RESTparams;
}

}
?>