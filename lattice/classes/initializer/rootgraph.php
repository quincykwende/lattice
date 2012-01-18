<?

Class Initializer_RootGraph {

	public function initialize(){
      try {
				Graph::object();
      } catch (Exception $e) {
         if ($e->getCode() == 1146) { //code for table doesn't exist
            $sqlFile = Kohana::find_file('config', 'graph-mysql', $ext = 'sql');
            $sql = file_get_contents($sqlFile[0]);
						mysql_multiquery($sql);	


            $sqlFile = Kohana::find_file('config', 'tags-mysql', $ext = 'sql');
            $sql = file_get_contents($sqlFile[0]);
						mysql_multiquery($sql);	
         }
      }

			$problems = 0;
			if(! (file_exists(DOCROOT.'application/media') && is_writable(DOCROOT.'application/media'))){
				Lattice_Initializer::addProblem('application/media must exist and be writable.  Use mkdir application/media; chmod 777 application/media');	
				$problems++;
			}
			if(! (file_exists(DOCROOT.'application/export') && is_writable(DOCROOT.'application/export'))){
				Lattice_Initializer::addProblem('application/export must exist and be writable.  Use mkdir application/export; chmod 777 application/export');	
				$problems++;
			}
			return $problems;
	}

}