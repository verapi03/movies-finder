<?php
  $url_search = "https://api.themoviedb.org/3/search/person?&query=".urlencode($_REQUEST["input"]);
  $object_search = new tmdb_api_call($url_search);
  $arr_movies_search = json_decode($object_search->get_movies(),true);
  echo var_dump($arr_movies_search);

  /**
   *  This class consumes the TMDb API services depending on the URL 
   *  passed on as reference to the class constructor in order to  
   *  get necessary info about movies.
   */
  class tmdb_api_call {
    protected $url;
    const api_key = "da978a15e0922d1624da0150e1a1fe19";

    /**
     *  Class constructor
     */
    public function __construct($url) {
        $this->url = $url;
    }

    /**
     *  This method makes GET requests with curl to the TMDb API.
     */
    public function get_movies() {
      $request =  $this->url.'&api_key='.self::api_key; 
      $ch = curl_init($request);
      curl_setopt($ch, CURLOPT_HEADER, false); 
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $curl_response = curl_exec($ch); 
      curl_close($ch); 
      return $curl_response;
    }
  }
?>
