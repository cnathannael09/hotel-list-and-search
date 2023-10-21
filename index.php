<?php 
$json_data = file_get_contents('response1.json');
$data = json_decode($json_data);

if ($data) {
    $statusCode = $data->Envelope->Body->HotelSearchResponse->Status->StatusCode;
    $description = $data->Envelope->Body->HotelSearchResponse->Status->Description;
    $hotels = $data->Envelope->Body->HotelSearchResponse->HotelResultList->HotelResult;

    foreach ($hotels as $hotel) {
        $hotelName = $hotel->HotelInfo->HotelName;
        $hotelDescription = $hotel->HotelInfo->HotelDescription;
    }
} else {
    echo "Failed to parse JSON data.";
}

$itemsPerPage = 10;
$totalItems = count($data->Envelope->Body->HotelSearchResponse->HotelResultList->HotelResult);
$totalPages = ceil($totalItems / $itemsPerPage);

$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$current_page = max(1, min($current_page, $totalPages));

$visiblePages = 5;

$startIndex = ($current_page - 1) * $itemsPerPage;
$endIndex = $startIndex + $itemsPerPage;
$hotels = $data->Envelope->Body->HotelSearchResponse->HotelResultList->HotelResult;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mandira Travel Test</title>

  <!-- CSS -->
  <link rel="stylesheet" href="src/assets/css/style.css">
  <link rel="stylesheet" href="src/assets/plugins/bootstrap/css/bootstrap.min.css" type="text/css">
  <link rel="stylesheet" href="src/assets/plugins/fontawesome/css/all.min.css" type="text/css">
</head>
<body class="bg-light">
  <nav class="navbar sticky-top navbar-expand navbar-white bg-white">
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <form class="form-inline my-2 my-lg-0" method="post">
        <input class="form-control mr-sm-2" type="text" name="searchQuery" id="searchQuery">
        <input class="btn btn-outline-success my-2 my-sm-0"  type="submit" value="Search">
      </form>
    </div>
  </nav>

  <div class="container mb-2">
    <?php
    echo '<p>Menampilkan '.$totalItems.' akomodasi terbaik dengan harga terbaik</p>';
    echo '<p>Tampilan Harga: </p>';
    ?>
    <form class="form-inline my-2 my-lg-0" method="post">
        <select class="form-control" name="sortOrder" id="sortOrder">
            <option value="asc">Low to High</option>
            <option value="desc">High to Low</option>
        </select>
        <input class="btn btn-outline-success my-2 my-sm-0" type="submit" value="Sort">
    </form>
  </div>
  

  <?php 
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchQuery = isset($_POST['searchQuery']) ? $_POST['searchQuery'] : '';

    $jsonFile = 'response1.json';
  
    if (file_exists($jsonFile)) {
        $jsonData = file_get_contents($jsonFile);
        $data = json_decode($jsonData, true);
  
        if (isset($data['Envelope']['Body']['HotelSearchResponse']['HotelResultList']['HotelResult'])) {
            $results = array();
  
            foreach ($data['Envelope']['Body']['HotelSearchResponse']['HotelResultList']['HotelResult'] as $hotel) {
                if (stripos($hotel['HotelInfo']['HotelName'], $searchQuery) !== false) {
                    $results[] = $hotel;
                }
            }

            if (isset($_POST['sortOrder'])) {
              $sortOrder = $_POST['sortOrder'];
              if ($sortOrder === 'asc') {
                  usort($results, function($a, $b) {
                      return (float) str_replace(',', '', $a['MinHotelPrice']['_PrefPrice'])
                           - (float) str_replace(',', '', $b['MinHotelPrice']['_PrefPrice']);
                  });
              } elseif ($sortOrder === 'desc') {
                  usort($results, function($a, $b) {
                      return (float) str_replace(',', '', $b['MinHotelPrice']['_PrefPrice'])
                           - (float) str_replace(',', '', $a['MinHotelPrice']['_PrefPrice']);
                  });
              }
            }
  
            if (!empty($results)) {
              echo '<div class="container">';
              $i = 0;
              foreach ($results as $result) {
                $hotelPicture = $result['HotelInfo']['HotelPicture'];
                $hotelName = $result['HotelInfo']['HotelName'];
                $hotelDescription = $result['HotelInfo']['HotelDescription'];
                $rating = $result['HotelInfo']['Rating'];
                $hotelAdress = $result['HotelInfo']['HotelAddress'];
                $prefPrice = $result['MinHotelPrice']['_PrefPrice'];
                $formattedPrefPrice = number_format($prefPrice, 0, '.', '.');
                $prefCurrency = $result['MinHotelPrice']['_PrefCurrency'];
                
                echo '<div class="card d-flex align-items-stretch h-100 p-1">';
                echo '<div class="row no-gutters">';
                echo '<div class="col-md-4">';
                echo '<img src="' . $hotelPicture . '" class="card-img img-fluid" alt="card'. $i .'">';
                echo '</div>';
                echo '<div class="col-md-8">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . $hotelName . '</h5>';
                echo '<div><a href="#" class="text-warning ">';
                if ($rating === 'OneStar'){
                  echo '<i class="fa fa-star"></i>';
                } else if ($rating === 'TwoStar'){
                  echo '<i class="fa fa-star"></i><i class="fa fa-star"></i>';
                } else if ($rating === 'ThreeStar'){
                  echo '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
                } else if ($rating === 'FourStar'){
                  echo '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
                } else if ($rating === 'FiveStar'){
                  echo '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
                }
                echo '</a>  &#x2022;  <a href="#" class="text-secondary "><u>'. $hotelAdress .'</u></a></div>';
                if (isset($hotel->HotelInfo->TripAdvisorRating)) {
                  $tripAdvisorRating = $hotel->HotelInfo->TripAdvisorRating;
                  if ($tripAdvisorRating <= 2.5)
                  {
                    echo '<p class="card-text"><a href="#" class="rounded text-white bg-danger pl-2 pr-2">'.$tripAdvisorRating.'</a><a href="#" class="text-danger"><strong> Poor</strong></a></p>';
                  } else {
                    echo '<p class="card-text"><a href="#" class="rounded text-white bg-primary pl-2 pr-2">'.$tripAdvisorRating.'</a> <a href="#" class="text-primary"><strong> Good</strong></a></p>';
                  }
                } else {
                  echo '<p class="card-text">Trip Advisor Rating is not available.</p>';
                }
                echo '<p class="card-text">' . $hotelDescription . '</p>';
                echo '<p class="card-text float-right text-danger">' .$prefCurrency.'<a href="#" class="font-weight-bold text-danger"> '. $formattedPrefPrice . '</a></p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                $i++;
              }
              echo '</div>';
            } else {
              echo '<div class="container">';
              for ($i = $startIndex; $i < $endIndex && $i < $totalItems; $i++) {
                $hotel = $hotels[$i];
                $hotelPicture = $hotel->HotelInfo->HotelPicture;
                $hotelName = $hotel->HotelInfo->HotelName;
                $hotelDescription = $hotel->HotelInfo->HotelDescription;
                $rating = $hotel->HotelInfo->Rating;
                $hotelAdress = $hotel->HotelInfo->HotelAddress;
                $prefPrice = $hotel->MinHotelPrice->_PrefPrice;
                $formattedPrefPrice = number_format($prefPrice, 0, '.', '.');
                $prefCurrency = $hotel->MinHotelPrice->_PrefCurrency;
                
                echo '<div class="card d-flex align-items-stretch h-100 p-1">';
                echo '<div class="row no-gutters">';
                echo '<div class="col-md-4">';
                echo '<img src="' . $hotelPicture . '" class="card-img img-fluid" alt="card'. $i .'">';
                echo '</div>';
                echo '<div class="col-md-8">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . $hotelName . '</h5>';
                echo '<div><a href="#" class="text-warning ">';
                if ($rating === 'OneStar'){
                  echo '<i class="fa fa-star"></i>';
                } else if ($rating === 'TwoStar'){
                  echo '<i class="fa fa-star"></i><i class="fa fa-star"></i>';
                } else if ($rating === 'ThreeStar'){
                  echo '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
                } else if ($rating === 'FourStar'){
                  echo '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
                } else if ($rating === 'FiveStar'){
                  echo '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
                }
                echo '</a>  &#x2022;  <a href="#" class="text-secondary "><u>'. $hotelAdress .'</u></a></div>';
                if (isset($hotel->HotelInfo->TripAdvisorRating)) {
                  $tripAdvisorRating = $hotel->HotelInfo->TripAdvisorRating;
                  if ($tripAdvisorRating <= 2.5)
                  {
                    echo '<p class="card-text"><a href="#" class="rounded text-white bg-danger pl-2 pr-2">'.$tripAdvisorRating.'</a><a href="#" class="text-danger"><strong> Poor</strong></a></p>';
                  } else {
                    echo '<p class="card-text"><a href="#" class="rounded text-white bg-primary pl-2 pr-2">'.$tripAdvisorRating.'</a> <a href="#" class="text-primary"><strong> Good</strong></a></p>';
                  }
                } else {
                  echo '<p class="card-text">Trip Advisor Rating is not available.</p>';
                }
                echo '<p class="card-text">' . $hotelDescription . '</p>';
                echo '<p class="card-text float-right text-danger">' .$prefCurrency.'<a href="#" class="font-weight-bold text-danger"> '. $formattedPrefPrice . '</a></p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
              }
              echo '</div>';
              
              echo '<nav aria-label="Page navigation example">';
              echo '<ul class="pagination justify-content-center">';
            
              if ($totalPages <= $visiblePages) {
                for ($page = 1; $page <= $totalPages; $page++) {
                  echo "<a href='?page=$page'>$page</a>";
                }
              } else {
                $halfVisible = floor($visiblePages / 2);
            
                if ($current_page <= $halfVisible) {
                  $startPage = 1;
                  $endPage = $visiblePages;
                } elseif ($current_page >= $totalPages - $halfVisible) {
                  $startPage = $totalPages - $visiblePages + 1;
                  $endPage = $totalPages;
                } else {
                  $startPage = $current_page - $halfVisible;
                  $endPage = $current_page + $halfVisible;
                }
            
                if ($startPage > 1) {
                  echo "<li class='page-item'><a class='page-link' href='?page=1'>1</a></li>";
                  echo '<span class="page-link current text-secondary">...</span>';
                }
            
                for ($page = $startPage; $page <= $endPage; $page++) {
                  if ($page == $current_page) {
                    echo "<span class='page-link current text-secondary'>$page</span>";
                  } else {
                    echo "<li class='page-item'><a class='page-link' href='?page=$page'>$page</a></li>";
                  }
                }
            
                if ($endPage < $totalPages) {
                  echo '<span class="page-link current text-secondary">...</span>';
                  echo "<li class='page-item'><a class='page-link' href='?page=$totalPages'>$totalPages</a></li>";
                }
              }
            
              echo '</ul>';
              echo '</nav>';
            }
        } else {
          echo '<p>No hotel data found in the JSON file.</p>';
        }
    } else {
      echo '<p>JSON file not found.</p>';
    }
  } else {
    echo '<div class="container">';
    for ($i = $startIndex; $i < $endIndex && $i < $totalItems; $i++) {
      $hotel = $hotels[$i];
      $hotelPicture = $hotel->HotelInfo->HotelPicture;
      $hotelName = $hotel->HotelInfo->HotelName;
      $hotelDescription = $hotel->HotelInfo->HotelDescription;
      $rating = $hotel->HotelInfo->Rating;
      $hotelAdress = $hotel->HotelInfo->HotelAddress;
      $prefPrice = $hotel->MinHotelPrice->_PrefPrice;
      $formattedPrefPrice = number_format($prefPrice, 0, '.', '.');
      $prefCurrency = $hotel->MinHotelPrice->_PrefCurrency;
      
      echo '<div class="card d-flex align-items-stretch h-100 p-1">';
      echo '<div class="row no-gutters">';
      echo '<div class="col-md-4">';
      echo '<img src="' . $hotelPicture . '" class="card-img img-fluid" alt="card'. $i .'">';
      echo '</div>';
      echo '<div class="col-md-8">';
      echo '<div class="card-body">';
      echo '<h5 class="card-title">' . $hotelName . '</h5>';
      echo '<div><a href="#" class="text-warning ">';
      if ($rating === 'OneStar'){
        echo '<i class="fa fa-star"></i>';
      } else if ($rating === 'TwoStar'){
        echo '<i class="fa fa-star"></i><i class="fa fa-star"></i>';
      } else if ($rating === 'ThreeStar'){
        echo '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
      } else if ($rating === 'FourStar'){
        echo '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
      } else if ($rating === 'FiveStar'){
        echo '<i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>';
      }
      echo '</a>  &#x2022;  <a href="#" class="text-secondary "><u>'. $hotelAdress .'</u></a></div>';
      if (isset($hotel->HotelInfo->TripAdvisorRating)) {
        $tripAdvisorRating = $hotel->HotelInfo->TripAdvisorRating;
        if ($tripAdvisorRating <= 2.5)
        {
          echo '<p class="card-text"><a href="#" class="rounded text-white bg-danger pl-2 pr-2">'.$tripAdvisorRating.'</a><a href="#" class="text-danger"><strong> Poor</strong></a></p>';
        } else {
          echo '<p class="card-text"><a href="#" class="rounded text-white bg-primary pl-2 pr-2">'.$tripAdvisorRating.'</a> <a href="#" class="text-primary"><strong> Good</strong></a></p>';
        }
      } else {
        echo '<p class="card-text">Trip Advisor Rating is not available.</p>';
      }
      echo '<p class="card-text">' . $hotelDescription . '</p>';
      echo '<p class="card-text float-right text-danger">' .$prefCurrency.'<a href="#" class="font-weight-bold text-danger"> '. $formattedPrefPrice . '</a></p>';
      echo '</div>';
      echo '</div>';
      echo '</div>';
      echo '</div>';
    }
    echo '</div>';
    
    echo '<nav aria-label="Page navigation example">';
    echo '<ul class="pagination justify-content-center">';
  
    if ($totalPages <= $visiblePages) {
      for ($page = 1; $page <= $totalPages; $page++) {
        echo "<a href='?page=$page'>$page</a>";
      }
    } else {
      $halfVisible = floor($visiblePages / 2);
  
      if ($current_page <= $halfVisible) {
        $startPage = 1;
        $endPage = $visiblePages;
      } elseif ($current_page >= $totalPages - $halfVisible) {
        $startPage = $totalPages - $visiblePages + 1;
        $endPage = $totalPages;
      } else {
        $startPage = $current_page - $halfVisible;
        $endPage = $current_page + $halfVisible;
      }
  
      if ($startPage > 1) {
        echo "<li class='page-item'><a class='page-link' href='?page=1'>1</a></li>";
        echo '<span class="page-link current text-secondary">...</span>';
      }
  
      for ($page = $startPage; $page <= $endPage; $page++) {
        if ($page == $current_page) {
          echo "<span class='page-link current text-secondary'>$page</span>";
        } else {
          echo "<li class='page-item'><a class='page-link' href='?page=$page'>$page</a></li>";
        }
      }
  
      if ($endPage < $totalPages) {
        echo '<span class="page-link current text-secondary">...</span>';
        echo "<li class='page-item'><a class='page-link' href='?page=$totalPages'>$totalPages</a></li>";
      }
    }
  
    echo '</ul>';
    echo '</nav>';
  }

  ?>

  <!-- JQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="src/assets/js/app.js"></script>
  <script src="src/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>