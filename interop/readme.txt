Round 2 Interop Test files

Info at: http://www.whitemesa.com/interop.htm.

Requires an SQL database, schema for MySQL is in database_round2.sql.

run client_round2_run.php to store test results.
view client_round2_results.php to see test results

server_round2.php implements base, GroupB and GroupC interop tests.


To setup an interop server:

1. Web server must alias url /soap_interop/ to the pear/SOAP/interop 
   directory.
2. index.php should be set for the default document.
3. mySQL should be set up, with a database called interop, schema 
   is in database_round2.sql.
4. WSDL files should be updated, replacing 'localhost' with the 
   correct server:port for your server.
5. client_round2_run.php and server_round2_test.php should not be 
   left under the web root, they are available for manual testing.
