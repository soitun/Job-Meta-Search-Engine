<?php ini_set('MAX_EXECUTION_TIME',-1); ?>
<?php
require_once "Careerjet_API.php" ;
require_once "simplyhired.php";
require_once "cb.php";
require_once "indeed.php"; 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Clone Script</title>
</head>

<body>
<form action="clone_up.php" method="get" enctype="multipart/form-data">
Search: <input type="text" name="search" />
  	<input type="text" name="location"  />
        <input type="submit" value="Search"  />
</form></div>
<?php
if (isset ($_GET['search']) && isset ($_GET['location']))
	{
		$search= $_GET['search'];
		$location= $_GET['location'];
		if (!empty ($search) && !empty ($location))
			{
				/*~~~~~~~~~~~~~~~~~~~~~~Safe Zone~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
				$client = new Indeed("");
				$params = array("q" => $search,"l" => $location,"limit" => 25 ,"sort" => 'date',"highlight" => 1,"userip" => $_SERVER['REMOTE_ADDR'] ,"useragent" => $_SERVER['HTTP_USER_AGENT']); //"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2)"
				$results = $client->search($params);
				
				
				$cjapi = new Careerjet_API('en_GB') ;
				$page = 1 ; # Or from parameters.
				$result = $cjapi->search(array( 'keywords' => $search , 'location' => $location , 'pagesize' => 90 ));
				
				$page_num=1;
				$srchApi = new SimplyHired_API();
				$srchApi->set_query($search);
				$srchApi->set_location($location);
				$srchApi->set_pagenum($page_num);
				$result_sh = $srchApi->search_sh();
						
				print 'Key Word : '.ucwords($search)."<br />";
				print 'Location : '.ucwords($location)."<br />";
				
				print 'Total Indeeed Jobs : '.$results['totalResults']."<br />";
				$in_total = $results['totalResults'];
				if (isset($result->hits))
					{
						echo "Total CareerJet Jobs : ".$result->hits."<br/>" ;
						$cj_total = $result->hits;
					}
				else
					{	
						$cj_total = 0;
						echo "Total CareerJet Jobs : ".$cj_total."<br/>" ;
					}
				$job_count_cb=CBAPI::getJobCount($search,$location);
				echo "Total CareerBuilder Jobs : ".$job_count_cb."<br />";
				if(isset ($result_sh->rq->tv)) 
					{
					$sh_total = $result_sh->rq->tv;
					echo "Total Simply Hired Jobs : ".$sh_total."<br />";
					}
				else
					{
						$sh_total = 0;
						echo "Total Simply Hired Jobs : ".$sh_total."<br />";
					}
				$total = $in_total + $cj_total+ $job_count_cb +$sh_total;
				echo '<strong>Total Avalible Jobs</strong> : '.$total;
				
				/*~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~*/
				echo "<br /><br /><br />";
				/*###########Function Area############*/
		if ($total == 0)
			{
				echo 'Oops! No Results!';
			}
		else
			{
					if ( $result->type == 'JOBS' )
						{
							$jobs = $result->jobs ;
							if (isset ($jobs))
								{
									foreach( $jobs as &$job )
										{
											$cj_title = strtolower($job->title);
											$cj_company = strtolower($job->company);
											if (isset($results['results']))
												{
													foreach ($results['results'] as $result)
														{		
															$in_company = strtolower($result['company']);
															$in_title = strtolower($result['jobtitle']);
															
															$ans1 = strcmp($cj_company,$in_company);
															$ans2 = strcmp($cj_title,$in_title);
															if ($ans1 == 0 && $ans2 == 0)
																{	
																	/*
																	echo '********************** Duplication Part *****************************'."<br />";
																	echo '********************** End Duplication Part **************************'."<br />";
																	*/
																}
															
														}
												}
												
										}
								}
			            
									
					}
					
					/*####################################*/
					/*SORTING FINAL START*/
						$time = time();
						
						/*CareerJet Api*/
						if (isset($jobs))
							{
								foreach( $jobs as &$job )
									{
										$cj_date[] =  array( strtotime($job->date) , $job->title , $job->company , $job->locations , $job->description , $job->url , $job->date , '<strong>Career Jet</strong>');
									}
							}
						else
							{
								$cj_date[] = array(0,'','','','','','','');
							}
						/*End CareerJet Api*/
						
						/*Indeed.com Api*/
						if (isset($results['results']) && !empty($results['results']) )
							{
								foreach ($results['results'] as $result)
									{
										$in_date[] = array ( strtotime($result['date']) , $result['jobtitle'] , $result['company'] , $result['formattedLocation'] , $result['snippet'] , $result['url'] , $result['date'] , '<strong>Indeed.com</strong>' );
									}
							}
						else
							{
								$in_date[] = array(0,'','','','','','','');
							}
						/*End indeed.com Api*/
						
						/*Career Builder Api*/
						@$results = CBAPI::getJobResults($search,$location, "", 0);
						if (isset ($results))
							{
								foreach( $results as $job  )
									{
										$cb_date[] = array ( strtotime($job->posted) , $job->title , $job->companyName , $job->location , $job->description , $job->applyURL , $job->posted , '<strong>Career Builder</strong>' );
									}
							}
						else
							{
								$cb_date[] = array(0,'','','','','','','');
							}
						/*End Career Builder Api*/
						
						/*Simply Hired Api*/
						if (isset ($result_sh->rs->r))
							{
								foreach ( @$result_sh->rs->r as $result ) 
									{
										$sh_date[] = array ( strtotime($result->dp) , (string)$result->jt , (string)$result->cn , (string)$result->loc , (string)$result->e , (string)$result->src['url'] , (string)$result->dp , '<strong>Simply Hired</strong>' );
									}
							}
						else{
								$sh_date[] = array(0,'','','','','','','');
							}

							$ans[] = array_merge($cj_date,$cb_date,$sh_date,$in_date);
						
							
						//print_r ($ans);				
													
						/*End Simply Hired Api*/
						
						//print_r ($cj_date);
						//print '<br />';
						//print_r ($in_date);
						//print '<br />';
						//print_r ($cb_date);
						//print '<br />';
						//print_r ($sh_date);
						//print '<br /><br />';
/*						
						$ans[] = array_merge($cj_date,$in_date,$cb_date,$sh_date); //merge three array
						//print_r ($ans);	
						print '<br /><br />';
*/						
						/*& Main Sorting Function Start &*/
				
						foreach ($ans as $aaa)
							{
								function compare ($a,$b)
								{
									return $b[0] - $a[0];
								}
								usort($aaa,"compare");
								foreach ($aaa as $arr)
								{
								 	//$c = count($aaa);
									//echo $c.'<br />';
									//print_r ($arr);
									//if ($arr[0] != 0)
									//{ 
										print '<strong>Unix Time : </strong>'.$arr[0].'<br />';
										print '<strong>Job Title : </strong>'.$arr[1].'<br />';
										print '<strong>Company Name : </strong>'.$arr[2].'<br />';
										print '<strong>Location : </strong>'.$arr[3].'<br />';
										print '<strong>Description : </strong>'.$arr[4].'<br />';
										print '<strong>URL : </strong>'.$arr[5].'<br />';
										print '<strong>Date : </strong>'.$arr[6].'<br />';
										print '<strong>API Name : </strong>'.$arr[7].'<br />';
										print '<br />';
									//}
								}
							}
						
						/*& End Main Sorting Function Start &*/

			}
		}
		else
		{
			echo 'All fields are Required.';
		}
	}
?>
</body>
</html>
