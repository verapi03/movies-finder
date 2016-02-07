<?php
  try {
    $user_input = $_REQUEST["input"];
    $url_search = "https://api.themoviedb.org/3/search/person?&query=".urlencode($user_input);
    $object_search = new tmdb_api_call($url_search);
    $arr_movies_search = json_decode($object_search->get_call(),true);
    $validator = new data_validator($user_input,$arr_movies_search);
    $search_validated = $validator->search_validation();
    $object_parser = new tmdb_parse_data($search_validated);
    $movies_list = $object_parser->extract_movies_by_actor_search();
    foreach ($movies_list as &$list) {
      $list["movies"] = tmdb_parse_data::chronological_order($list["movies"]);
    }
    unset($list);
    $search_output = new movies_outputter($movies_list);
    echo $search_output->string_output();
  }
  catch(Exception $err) {
    echo $err->getMessage();
  }

  /**
   *  The objects of this class consumes the TMDb API services depending 
   *  on the URL passed on as reference to the class constructor in order 
   *  to get necessary info about movies.
   */
  class tmdb_api_call {
    protected $url;
    const api_key = "da978a15e0922d1624da0150e1a1fe19";
    public function __construct($url) {
      $this->url = $url;
    }
    /**
     *  This method makes GET requests with curl to the TMDb API.
     */
    public function get_call() {
      $request =  $this->url.'&api_key='.self::api_key; 
      $ch = curl_init($request);
      curl_setopt($ch, CURLOPT_HEADER, false); 
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $curl_response = curl_exec($ch); 
      curl_close($ch); 
      return $curl_response;
    }
  }

  /**
   *  This is a helper class used to validate the ouput from the 
   *  tmdb_api_call methods.
   */
  class data_validator {
    public $user_input;
    public $api_output;

    public function __construct($user_input, $api_output) {
      $this->user_input = $user_input;
      $this->api_output = $api_output;
    }
    /**
     *  This method validates the output of the search service of 
     *  the TMDb API eliminating the data that corresponds to
     *  actors who do not match the user input.
     */
    public function search_validation() {
      $misspelled_names = array();
      if(empty($this->api_output['results'])) {
        throw new Exception("Your input does not throw any results.");
      }
      foreach ($this->api_output['results'] as $key => $actor) {
        if (strcmp(strtolower($actor['name']), strtolower($this->user_input)) === 0) {
          if (empty($actor['known_for'])) {
            unset($this->api_output['results'][$key]);
          }
        } else {
          $misspelled_names[] = $actor['name'];
          unset($this->api_output['results'][$key]);
        }
      }
      if(empty($this->api_output['results'])) {
        $possible_names = implode(" or ",array_unique($misspelled_names));
        throw new Exception("Perhaps you mean ".$possible_names."?");
      }
      return $this->api_output;
    }
  }

  /**
   *  This class analyzes the outputs from the TMDb API calls and creates  
   *  new structures from the result.  
   */
  class tmdb_parse_data {
    public $data;
    public function __construct($data) {
      $this->data = $data;
    }
    /**
     *  This method takes the output of the API search service and makes 
     *  up an array of actors with their respective group of movies, 
     *  taking off all information different to the movies' titles 
     *  and their release dates.
     */
    public function extract_movies_by_actor_search() {
      date_default_timezone_set('America/New_York');
      $movies_by_actor = array();
      foreach ($this->data['results'] as $actor_features) {
        $actor = array();
        $actor['name'] = $actor_features['name'];
        $actor['movies'] = array();
        foreach ($actor_features['known_for'] as $movie_info) {
          $actor['movies'][] = array(
            "title"=>$movie_info['original_title'], 
            "release"=>date_utilities::instance()->date_to_timestamp($movie_info['release_date'])
          );
        }
        $movies_by_actor[] = $actor;
      }
      return $movies_by_actor;
    }
    /**
     *  This method puts in chronological order the list of movies.
     */
    public static function chronological_order($movies) {
      usort($movies, function ( $arr1, $arr2 ) {
        return $arr1["release"] - $arr2["release"];
      });
      return $movies;
    }
  }

  /**
   *  This class contains the logic to handle how the movies info is displayed.
   */
  class movies_outputter {
    public $movies_list;
    public function __construct($movies_list) {
      $this->movies_list = $movies_list;
    }
    /**
     *  This method returns the info about movies in a string format.
     */
    public function string_output() {
      $string_response = "";
      foreach ($this->movies_list as $actor) {
        $string_response .= "Artist: ".$actor["name"]."\n";
        $string_response .= "Movies:\n";
        foreach ($actor["movies"] as $movies) {
          if (date_utilities::instance()->is_timestamp($movies["release"])) {
            $movies["release"] = 
              date_utilities::instance()->timestamp_to_date($movies["release"]);
          }
          $string_response .= "Title: ".$movies["title"].". Release date: ".$movies["release"]."\n";
        }
        $string_response .= "\n";
      }
      return $string_response;
    }
  }

  /**
   *  This class defines a set of methods that perform common operations 
   *  on date type variables.  
   */
  class date_utilities {
    private static $instance = null;
    private function __construct(){}
    /** 
     *  This method allows to create only one instance of the 
     *  utility class.
     */
    public static function instance(){
      if (self::$instance === null) {
          self::$instance = new self();
      }
      return self::$instance;
    }
    /**
     *  This method evaluates if there is anything else than digits 
     *  inside the string so the string is not timestamp.
     */
    public static function is_timestamp($str) {
      return !preg_match('/[^\d]/', $str);
    }
    /**
     *  This method converts a timestamp input to the TMDb date format.
     */
    public static function timestamp_to_date($int) {
      return date("Y-m-d", $int);
    }
    /**
     *  This method converts a date format input to timestamp.
     */
    public static function date_to_timestamp($str) {
      return strtotime($str);
    }
  }
?>
