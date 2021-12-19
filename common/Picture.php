<?php
class Picture{
    private $fileName;
    private $id;
    private $description;
    private $title;
    private $comments;
    private $album_id;
    private $owner_id;

    public static function getPictures($Pdo, $albumId){
        $sql = "SELECT p.picture_id,  p.fileName, p.description, p.title, p.album_id, a.owner_id  FROM picture p INNER JOIN album a "
                . " ON a.album_id=p.album_id WHERE p.album_id = :album_id";
        $pStmt = $Pdo->prepare($sql);
        $pStmt->execute(["album_id" => $albumId]);
        $pic_info = $pStmt->fetchAll();

        $pictures = array();
        
        $numFiles = count($pic_info);
        if($numFiles>0){
        foreach($pic_info as $pic){
            $picture = new Picture($pic[1], $pic[0], $pic[2], $pic[3], $pic[4],$pic[5]);
            array_push($pictures, $picture);
        }
        return $pictures;
        }
        else{
           $pictures = null;
        }
    }

    
    
    public static function getPicture($Pdo, $pictureId){
        $sql = "SELECT distinct p.picture_id,  p.fileName, p.description, p.title, p.album_id, a.owner_id FROM picture p "
                . " INNER JOIN album a"
                . " ON a.album_id=p.album_id"
                . " WHERE p.picture_id = :picture_id";
        $pStm = $Pdo->prepare($sql);
        $pStm->execute(["picture_id" => $pictureId]);
        $pic_inf = $pStm->fetch();
        
       if($pic_inf && count($pic_inf)>0){
           $picture = new Picture($pic_inf[1], $pic_inf[0], $pic_inf[2], $pic_inf[3], $pic_inf[4],$pic_inf[5]);
       }
       else{
           $picture=null;
       }
       return $picture;
    }

    
    
    
    public function __construct($fileName, $id, $description, $title, $album_id, $owner_id){
        $this->fileName = $fileName;
        $this->id = $id;
        $this->description = $description;
        $this->title = $title;
        $this->album_id=$album_id;
        $this->owner_id=$owner_id;
    }

    public function getId(){
        return $this->id;
    }

    public function getName(){
        return $this->fileName;
    }

    public function getDescription(){
        return $this->description;
    }

    public function getTitle(){
        return $this->title;
    }
public function getAlbumId(){
        return $this->album_id;
    }
    public function getOwnerId(){
        return $this->owner_id;
    
    }
    public function getComments($Pdo){
        if(!$this->comments){
            //get list of pics that belong to an album from DB and store in an array
            $sql = "SELECT c.Comment_Text, u.Name, DATE_FORMAT(c.Date, '%Y-%m-%d') "
                   ."FROM comment c "
                   ."INNER JOIN user u ON c.Author_Id = u.UserId "
                   ."WHERE c.Picture_Id = :pictureId "
                   ."ORDER by c.Date DESC";
            $pStmt = $Pdo->prepare($sql);
            $pStmt->execute(["pictureId" => $this->getId()]);
            $this->comments = $pStmt->fetchAll();
        }
        return $this->comments;
    }

    public function getAlbumFilePath(){
        return ALBUM_PICTURES_DIR."/watermarked".$this->fileName;
    }

    public function getThumbnailFilePath(){
        return THUMBNAILS_DIR."/".$this->fileName;
    }

    public function getOriginalFilePath(){
        return ORIGINAL_PICTURES_DIR."/".$this->fileName;
    }
    
    
    public function rotatePicture($value){
        rotateImage($this->getAlbumFilePath(), $value);
        rotateImage($this->getThumbnailFilePath(), $value);
        rotateImage($this->getOriginalFilePath(), $value);
    }

    public function downloadFile(){
        $file = $this->getOriginalFilePath();
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        flush();
    }
    
    public function deleteFile($Pdo){
        $returnMessage = "";
        try{
            //deletes picture comments if there's any
            $sql = "DELETE FROM comment where picture_id = :pictureId ";
            $pStmt = $Pdo->prepare($sql);
            $pStmt->execute(["pictureId" => $this->getId()]);
            
            //deletes picture information
            $sql = "DELETE FROM picture where picture_id = :pictureId ";
            $pStmt = $Pdo->prepare($sql);
            $pStmt->execute(["pictureId" => $this->getId()]);
            // if Deleted pic info sucessfully
            //deletes files from albums
            unlink($this->getAlbumFilePath());
            unlink($this->getThumbnailFilePath());
            unlink($this->getOriginalFilePath());
        } catch(PDOException $e){
            $returnMessage = $e->getMessage();
        }
        return $returnMessage;
    }

}