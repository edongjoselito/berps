<?php

  function confirm_query($result_set){
  global $con; 

  if(!$result_set){
    die("Database query failed." . mysqli_error($con));
  }
}
  function prep($string){
    global $con;
  
    $safe_string = mysqli_real_escape_string($con, $string);
    return $safe_string;
  }
  
  function encodeToUtf8($string) {
  return mb_convert_encoding($string, "UTF-8", mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true));
  }
  
  function find_report_of_grade($student_number){
    global $con;

    $safe_student_number = prep($student_number);
  
    $sql  = "SELECT * ";
    $sql .= "FROM grades ";
    $sql .= "WHERE StudentNumber = '{$safe_student_number}' ";
    $sql .= "ORDER BY SY, Semester, SubjectCode ASC";
    $result_set = mysqli_query($con, $sql);
    confirm_query($result_set);
    return $result_set;
  }
  
  function find_subject_by_subject_code($subject_code){
  global $con;
  $safe_subject_code = prep($subject_code);
  
  $sql  = "SELECT * ";
  $sql .= "FROM subjects ";
  $sql .= "WHERE SubjectCode = '{$safe_subject_code}'";
  $sql .= "LIMIT 1";
  $subject_set = mysqli_query($con, $sql);
  confirm_query($subject_set);
  if($subject = mysqli_fetch_assoc($subject_set)){
    return $subject;
  } else {
    return null;
  }
}

function find_student_by_id($student_number){
  global $con;
  $safe_student_number = prep($student_number);
  
  $sql  = "SELECT * ";
  $sql .= "FROM studeprofile ";
  $sql .= "WHERE StudentNumber = '{$safe_student_number}'";
  $sql .= "LIMIT 1";
  $student_set = mysqli_query($con, $sql);
  confirm_query($student_set);
  if($student = mysqli_fetch_assoc($student_set)){
    return $student;
  } else {
    return null;
  }
}

function find_all_course(){
  global $con;
  
  $sql  = "SELECT * ";
  $sql .= "FROM course_table ";
  $sql .= "ORDER BY CourseDescription ASC";
  $event_set = mysqli_query($con, $sql);
  confirm_query($event_set);
  return $event_set;
}

function find_2_course(){
  global $con;
  
  $sql  = "SELECT * ";
  $sql .= "FROM course_table ";
  $sql .="WHERE Duration='2 Years'";
  $sql .= "ORDER BY CourseDescription ASC";
  $event_set = mysqli_query($con, $sql);
  confirm_query($event_set);
  return $event_set;
}

function find_all_course1(){
  global $con;
  
  $sql  = "SELECT * ";
  $sql .= "FROM course_table ";
  $sql .="WHERE Duration='4 Years'";
  $sql .= "ORDER BY CourseDescription ASC";
  $event_set = mysqli_query($con, $sql);
  confirm_query($event_set);
  return $event_set;
}

function find_subject_by_yearlevel_semester_course ($yearlevel, $semester, $course){
  global $con;
  $safe_yearlevel = prep($yearlevel);
  $safe_semester = prep($semester);
  $safe_course = prep($course);

  $sql  = "SELECT * ";
  $sql .= "FROM subjects ";
  $sql .= "WHERE Semester = '{$safe_semester}' ";
  $sql .= "AND Course = '{$safe_course}' ";
  //if($safe_yearlevel != '1-Yr. Electronic Data Processing (EDP)'){
  $sql .= "AND YearLevel = '{$safe_yearlevel}' ";
  //}
  //$sql .= "AND SemEffective = '1st Sem.' ";
  //$sql .= "AND Effectivity = '1st Sem., SY 2015-2016' ";
  $sql .= "ORDER BY SubjectCode ASC";
  $student_set = mysqli_query($con, $sql);
  confirm_query($student_set);
  return $student_set;
}

function find_grade_by_subject_description($sn, $sd){
  global $con;

  $safe_sn = prep($sn);
  $safe_sd = prep($sd);
  
  $sql  = "SELECT * ";
  $sql .= "FROM grades ";
  $sql .= "WHERE StudentNumber = '{$safe_sn}' ";
  $sql .= "AND Description = '{$safe_sd}' ";
  $sql .= "LIMIT 1";
  $grade_set = mysqli_query($con, $sql);
  confirm_query($grade_set);
  if($grade = mysqli_fetch_assoc($grade_set)){
    return $grade;
  } else {
    return null;
  }
}

function color_state(){ 
	global $grade;

    if($grade['Equivalent'] == '7.0'){echo 'class="blue-color"'; }
	elseif($grade['Equivalent'] == '9.0'){echo 'class="orange-color"';}
	elseif($grade['Equivalent'] == '5.0'){echo 'class="red-color"';}

	else {
	if($grade['Equivalent'] == '5.0'){echo 'class="red-color"'; }
 	}
}

function find_sc_description_section_instructor($sc, $description, $section, $instructor){
  global $con;

  $safe_sc = prep($sc);
  $safe_description = prep($description);
  $safe_section = prep($section);
  $safe_instructor = prep($instructor);
  
  $sql  = "SELECT * ";
  $sql .= "FROM grades ";
  $sql .= "WHERE SubjectCode = '{$safe_sc}' ";
  $sql .= "AND Description = '{$safe_description}' ";
  $sql .= "AND Section = '{$safe_section}' ";
  $sql .= "AND Instructor = '{$safe_instructor}'";
  $grade_set = mysqli_query($con, $sql);
  confirm_query($grade_set);
  return $grade_set;
}

?>