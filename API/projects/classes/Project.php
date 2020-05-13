<?php
/**
 * Created by PhpStorm.
 * User: karl
 * Date: 2019-05-22
 * Time: 09:52
 */

namespace Moycroft\API\projects\project;


use Moycroft\API\accounts\Account\Account;
use Moycroft\API\internal\mysql\Connect;
use function Moycroft\API\helper\IMPORT;
use function Moycroft\API\internal\GUID\GUID;
use function Moycroft\API\internal\reporting\report\__error;
use function Moycroft\API\internal\reporting\report\__infoSH;
use function Moycroft\API\internal\reporting\report\__returnHTTPException;

IMPORT("API.internal.*");
IMPORT("API.accounts.*");
class Project
{
    private $connection;
    const STATELIST = array(
        'AL'=>'ALABAMA',
        'AK'=>'ALASKA',
        'AS'=>'AMERICAN SAMOA',
        'AZ'=>'ARIZONA',
        'AR'=>'ARKANSAS',
        'CA'=>'CALIFORNIA',
        'CO'=>'COLORADO',
        'CT'=>'CONNECTICUT',
        'DE'=>'DELAWARE',
        'DC'=>'DISTRICT OF COLUMBIA',
        'FM'=>'FEDERATED STATES OF MICRONESIA',
        'FL'=>'FLORIDA',
        'GA'=>'GEORGIA',
        'GU'=>'GUAM GU',
        'HI'=>'HAWAII',
        'ID'=>'IDAHO',
        'IL'=>'ILLINOIS',
        'IN'=>'INDIANA',
        'IA'=>'IOWA',
        'KS'=>'KANSAS',
        'KY'=>'KENTUCKY',
        'LA'=>'LOUISIANA',
        'ME'=>'MAINE',
        'MH'=>'MARSHALL ISLANDS',
        'MD'=>'MARYLAND',
        'MA'=>'MASSACHUSETTS',
        'MI'=>'MICHIGAN',
        'MN'=>'MINNESOTA',
        'MS'=>'MISSISSIPPI',
        'MO'=>'MISSOURI',
        'MT'=>'MONTANA',
        'NE'=>'NEBRASKA',
        'NV'=>'NEVADA',
        'NH'=>'NEW HAMPSHIRE',
        'NJ'=>'NEW JERSEY',
        'NM'=>'NEW MEXICO',
        'NY'=>'NEW YORK',
        'NC'=>'NORTH CAROLINA',
        'ND'=>'NORTH DAKOTA',
        'MP'=>'NORTHERN MARIANA ISLANDS',
        'OH'=>'OHIO',
        'OK'=>'OKLAHOMA',
        'OR'=>'OREGON',
        'PW'=>'PALAU',
        'PA'=>'PENNSYLVANIA',
        'PR'=>'PUERTO RICO',
        'RI'=>'RHODE ISLAND',
        'SC'=>'SOUTH CAROLINA',
        'SD'=>'SOUTH DAKOTA',
        'TN'=>'TENNESSEE',
        'TX'=>'TEXAS',
        'UT'=>'UTAH',
        'VT'=>'VERMONT',
        'VI'=>'VIRGIN ISLANDS',
        'VA'=>'VIRGINIA',
        'WA'=>'WASHINGTON',
        'WV'=>'WEST VIRGINIA',
        'WI'=>'WISCONSIN',
        'WY'=>'WYOMING',
        'AE'=>'ARMED FORCES AFRICA \ CANADA \ EUROPE \ MIDDLE EAST',
        'AA'=>'ARMED FORCES AMERICA (EXCEPT CANADA)',
        'AP'=>'ARMED FORCES PACIFIC'
    );
    public function __construct()
    {
        $this->connection = new Connect();
        $this->connection->connect();
    }
    public function search($query){
        $keywords = array_filter(explode(" ", $query));
        if (sizeof($keywords) == 0){
            return [];
        }
        $results = [];
        foreach ($keywords as $keyword){
            $query = "SELECT * FROM `projects_all_listing` WHERE LOWER(CONCAT(`project_name`, '', `project_number`, '', `project_location_state`, '', `project_location_address`, '', `project_ID`, '')) LIKE LOWER('%$keyword%') LIMIT 30";
//            array_push($results, $query);
            array_push($results,
                $this->connection->getData(
                    $this->connection->query(
                        $query
                    )
                )
            );
        }
        $cleanedResults = [];
        foreach ($results[0] as $result) {
            if ($this->cleanSearch($keywords, $result)) {
                array_push($cleanedResults, $result);
            }
        }
        $formatedResults = [];
        foreach ($cleanedResults as $result) {
            array_push($formatedResults, [
                "ID" => $result['project_ID'],
                "name" => $result['project_name'],
                "state" => $result['project_location_state'],
                "address" => $result['project_location_address'],
                "completed" => $result['project_completed'],
                "number" => $result['project_number']
            ]);
        }
        return $formatedResults;

    }
    public static function getProjectName($projectID){
        $conn = new Connect();
        $conn->connect();
        $res = $conn->getData($conn->query("SELECT project_name FROM projects_all_listing where project_ID='$projectID'"));
        if (! isset($res[0]['project_name'])){
            return false;
        }else{
            return $res[0]['project_name'];
        }

    }
    public static function getProjectAddress($projectID){
        $conn = new Connect();
        $conn->connect();
        $res = $conn->getData($conn->query("SELECT project_location_address FROM projects_all_listing where project_ID='$projectID'"));
        if (! isset($res[0]['project_location_address'])){
            return false;
        }else{
            return $res[0]['project_location_address'];
        }

    }
    public static function getAllProjects(){
        $conn = new Connect();
        $conn->connect();
        $res = $conn->getData($conn->query("SELECT * FROM projects_all_listing;"));
        return $res;
    }
    public static function getProjectState($projectID){
        $conn = new Connect();
        $conn->connect();
        $res = $conn->getData($conn->query("SELECT project_location_state FROM projects_all_listing where project_ID='$projectID'"));
        if (! isset($res[0]['project_location_state'])){
            return false;
        }else{
            return $res[0]['project_location_state'];
        }

    }
    public static function getProjectManager($projectID){
        $conn = new Connect();
        $conn->connect();
        $res = $conn->getData($conn->query("SELECT project_location_state FROM projects_all_listing where project_ID='$projectID'"));
        if (! isset($res[0]['project_location_state'])){
            return false;
        }else{
            return $res[0]['project_location_state'];
        }

    }
    public static function getProjectStatus($projectID){
        $conn = new Connect();
        $conn->connect();
        $res = $conn->getData($conn->query("SELECT project_manager FROM projects_all_listing where project_ID='$projectID'"));
        if (! isset($res[0]['project_manager'])){
            return false;
        }else{
            return $res[0]['project_manager'];
        }

    }

    public static function getProjectNumber($projectID)
    {

        if ($projectID == null) {
            return false;
        }
        $conn = new Connect();
        $conn->connect();
        $res = $conn->getData($conn->query("SELECT project_number FROM projects_all_listing where project_ID='$projectID'"));
        if (!isset($res[0]['project_number'])) {
            return false;
        } else {
            return $res[0]['project_number'];
        }

    }
    public function editProject($id, $name, $address, $state, $description, $manager=null, $company=null){
        //Filter request
        if (!isset(self::STATELIST[$state])){
            __error("Malformed request - Invalid state", true);
        }

        if ($manager !== "") {
            $manager = filter_var($manager, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            $account = new Account();
            if (!$account->accountExists($manager)){
                __error("Malformed request - Invalid Account", true);
            }else{
                $manager = "'$manager'";
            }
        }else{
            $manager = "NULL";
        }
        if ($company !== "") {
            $company = filter_var($company, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            $company = "'$company'";
        }else{
            $company = "NULL";
        }

        $description = filter_var($description, FILTER_SANITIZE_NUMBER_INT);
        $name = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $address = filter_var($address, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);



        $prn = (Project::getProjectNumber($id));
        $this->connection->query("DELETE FROM projects_all_listing where project_ID = '$id'");
        $this->connection->query("INSERT INTO `projects_all_listing` ( `project_ID`, `project_name`, `company_ID`, `project_number`, `project_location_state`,`project_manager`, `project_location_address`, `project_visable`, `project_completed`) VALUES ( '$id', '$name', $company, '".$prn."','$state', $manager,  '$address', '1', '0')");
        __infoSH("Project '$name' @ $id has been successfully created'", "success");
        return $id;
    }
    public function createProject($name, $address, $state, $description, $manager=null, $company=null){
        //Filter request
        if (!isset(self::STATELIST[$state])){
            __error("Malformed request - Invalid state", true);
        }

        if ($manager !== "") {
            $manager = filter_var($manager, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            $account = new Account();
            if (!$account->accountExists($manager)){
                __error("Malformed request - Invalid Account", true);
            }else{
                $manager = "'$manager'";
            }
        }else{
            $manager = "NULL";
        }
        if ($company !== "") {
            $company = filter_var($company, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            $company = "'$company'";
        }else{
            $company = "NULL";
        }
        $description = filter_var($description, FILTER_SANITIZE_NUMBER_INT);
        $name = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $address = filter_var($address, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);


        //Check for duplicate insertion
        if (sizeof($this->connection->getData($this->connection->query("SELECT * FROM `projects_all_listing` WHERE (`project_name` = '$name')"))) !== 0){
            __error("Duplicate Entry: Project already exists. ", true);
        }
        $id = GUID("API.project", "Project GUID", false);
        $this->connection->query("INSERT INTO `projects_all_listing` ( `project_ID`, `project_name`, `company_ID`, `project_number`, `project_location_state`,`project_manager`, `project_location_address`, `project_visable`, `project_completed`) VALUES ( '$id', '$name', $company, '".($this->getHighestProjectNumber()+1)."','$state', $manager,  '$address', '1', '0')");
        __infoSH("Project '$name' @ $id has been successfully created'", "success");
        return $id;
    }
    public function getHighestProjectNumber(){
        $hpn = ($this->connection->getData($this->connection->query("SELECT MAX(project_number) FROM projects_all_listing; ")))[0]['MAX(project_number)'];
        if (date("y") != substr($hpn,0,2)){
            $hpn = date("y") . "000";
        }
        return( $hpn );
    }

    public function getProjectCount(){
        return $this->connection->getData($this->connection->query("SELECT COUNT(*) FROM projects_all_listing;"))[0]['COUNT(*)'];
    }
    public function getProjects($start, $end){
        return $this->connection->getData($this->connection->query("SELECT * FROM projects_all_listing ORDER BY project_number DESC LIMIT $start, $end"));
    }
    private function cleanSearch($keywords, $result){
        $searchString = "";
        foreach ($result as $item) {
            $searchString .= $item . " ";
        }
        foreach ($keywords as $keyword){
            if (!strpos($searchString, $keyword)){
                return false;
            }
        }
        return true;
    }
}

function __post($action, $incoming)
{
    set_error_handler("Moycroft\IO\switchboard\HTTPErrorSend");
    $project = new Project();
    if ($action == "search") {
        echo json_encode($project->search($incoming['query']));
    }elseif ($action == "list"){

        if (isset($incoming['section'])){
            $projects = [];
            foreach ($project->getProjects(intval($incoming['section'])*10,10) as $item){
                array_push($projects, [
                    "name" => $item['project_name'],
                    "id" => $item['project_ID'],
//                    "company" => $item['company_ID'],
                    "address" => $item['project_location_address'],
                    "state" => $item['project_location_state'],
                    "number" => $item['project_number'],
                    "completed" => $item['project_completed'],
                    "managers" => $item['project_manager'] ? (new Account())->getFirstName($item['project_manager']) . " " . (new Account())->getLastName($item['project_manager']) : "NOT SET",
                    "managerID" => $item['project_manager']

                ]);
            }
        $return = [
            "count" => $project->getProjectCount(),
            "projects" => $projects
        ];

        echo json_encode($return);
        }
    }elseif ($action == "export"){
        $eval = "";
        IMPORT("API.accounts.*");
        $acc = new Account();
        foreach (Project::getAllProjects() as $item){
            $item['project_manager'] = $acc->getFirstName($item['project_manager']) . " " . $acc->getLastName($item['project_manager']);
            $retval = [
                "Name" => urlencode(isset($item['project_name']) ? $item['project_name'] : "N/A"),
                "Number" => urlencode(isset($item['project_number']) ? $item['project_number'] : "N/A"),
                "Manager" => urlencode(isset($item['project_manager']) ? $item['project_manager'] : "N/A"),
                "State" => urlencode(isset($item['project_location_address']) ? $item['project_location_state'] : "N/A"),
                "Address" => urlencode(isset($item['project_location_state']) ? $item['project_location_state'] : "N/A"),
                "Completed" => urlencode(isset($item['project_completed']) ? $item['project_completed'] : "N/A")
            ];
            $eval .= json_encode($retval) . "|%%|";
        }
        echo $eval;
//        echo (json_encode(Project::getAllProjects()));
    }elseif ($action == "create"){
        if (!isset($incoming['name'], $incoming['description'], $incoming['address'],  $incoming['state'])){
            __returnHTTPException("Malformed Request: ", "Perams missing");
        }elseif ((isset($incoming['modify']) && $incoming['modify'] == true) && ((isset($incoming['id'])) && Project::getProjectName($incoming['id'])) && isset($incoming['name'], $incoming['description'], $incoming['address'],  $incoming['state'])){
            $project->editProject($incoming['id'],$incoming['name'], $incoming['address'], $incoming['state'], $incoming['description'], $incoming['project_manager'], $incoming['company']);
        }else {
            $project->createProject($incoming['name'], $incoming['address'], $incoming['state'], $incoming['description'], $incoming['project_manager'], $incoming['company']);
        }

    }

}