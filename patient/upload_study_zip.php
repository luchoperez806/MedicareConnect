<?php
session_start();
require_once "../includes/db.php";

header('Content-Type: application/json');

// ==============================
// 1) Validar paciente
// ==============================
$patient_id = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;

if(!$patient_id){
    echo json_encode(["success"=>false, "message"=>"Paciente no identificado"]);
    exit;
}

// ==============================
// 2) Validar archivo
// ==============================
if(!isset($_FILES['zipfile'])){
    echo json_encode(["success"=>false, "message"=>"No se envió archivo ZIP"]);
    exit;
}

$file = $_FILES['zipfile'];

if($file['error'] !== 0){
    echo json_encode(["success"=>false, "message"=>"Error al subir ZIP"]);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if($ext !== "zip"){
    echo json_encode(["success"=>false, "message"=>"Formato inválido. Debe ser .zip"]);
    exit;
}

// ==============================
// 3) Carpetas
// ==============================
$baseDir = "../uploads/";

if(!is_dir($baseDir)){
    mkdir($baseDir,0777,true);
}

$tempDir = $baseDir . "dicom_tmp_".time();
mkdir($tempDir);

// mover a temp
$zipPath = $tempDir."/upload.zip";
move_uploaded_file($file['tmp_name'],$zipPath);

// ==============================
// 4) Extraer
// ==============================
$zip = new ZipArchive;
if($zip->open($zipPath) !== true){
    echo json_encode(["success"=>false,"message"=>"No se pudo abrir el ZIP"]);
    exit;
}

$zip->extractTo($tempDir);
$zip->close();
unlink($zipPath);

// ==============================
// 5) Buscar dicoms
// ==============================
$dicoms = [];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($tempDir)
);

foreach($it as $f){

    if($f->isDir()) continue;

    if(strtolower($f->getExtension()) === "dcm"){

        $newName = uniqid().".dcm";
        $dest = $baseDir.$newName;

        copy($f->getPathname(),$dest);

        $dicoms[]=$newName;

        $stmt = $pdo->prepare("
            INSERT INTO studies(patient_id,file_name,uploaded_at,type)
            VALUES(?,?,NOW(),'dicom')
        ");
        $stmt->execute([$patient_id,$newName]);
    }
}

// ==============================
// 6) limpiar temp
// ==============================
function deleteDir($p){
    if(!file_exists($p)) return;
    foreach(scandir($p) as $a){
        if($a=='.'||$a=='..') continue;
        $f="$p/$a";
        if(is_dir($f)) deleteDir($f);
        else unlink($f);
    }
    rmdir($p);
}

deleteDir($tempDir);

// ==============================
// FIN
// ==============================
if(!$dicoms){
    echo json_encode(["success"=>false, "message"=>"El ZIP no contenía archivos DICOM"]);
    exit;
}

echo json_encode([
    "success"=>true,
    "message"=>"Estudios subidos correctamente",
    "count"=>count($dicoms)
]);
