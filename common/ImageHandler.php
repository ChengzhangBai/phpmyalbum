<?php
function save_uploaded_file($destinationPath, $file, $fileIndex, $fileId)
    {
        if (!file_exists($destinationPath))
        {
            mkdir($destinationPath);
        }
        
        $tempFilePath = $file['tmp_name'][$fileIndex];
        $filePath = $destinationPath."/".$file['name'][$fileIndex];
        
        $pathInfo = pathinfo($filePath);
        $dir = $pathInfo['dirname'];
        $ext = $pathInfo['extension'];
        $fileName = $fileId.'.'.$ext;
        
        $filePath = $destinationPath."/".$fileName;

        //make sure not to overwrite existing files 
        $i="";
        while (file_exists($filePath))
        {	
            $i++;
            $filePath = $dir."/".$fileName."_".$i.".".$ext;
        }
        move_uploaded_file($tempFilePath, $filePath);
        
        return $filePath;
    }

    function resamplePicture($filePath, $destinationPath, $maxWidth, $maxHeight)
    {
        if (!file_exists($destinationPath))
        {
            mkdir($destinationPath);
        }

        $imageDetails = getimagesize($filePath);
        
        $originalResource = null;
        if ($imageDetails[2] == IMAGETYPE_JPEG) 
        {
            $originalResource = imagecreatefromjpeg($filePath);
        } 
        elseif ($imageDetails[2] == IMAGETYPE_PNG) 
        {
            $originalResource = imagecreatefrompng($filePath);
        } 
        elseif ($imageDetails[2] == IMAGETYPE_GIF) 
        {
            $originalResource = imagecreatefromgif($filePath);
        }
        $widthRatio = $imageDetails[0] / $maxWidth;
        $heightRatio = $imageDetails[1] / $maxHeight;
        $ratio = max($widthRatio, $heightRatio);
        
        
        
        $newWidth = $imageDetails[0] / $ratio;
        $newHeight = $imageDetails[1] / $ratio;
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        $success = imagecopyresampled($newImage, $originalResource, 0, 0, 0, 0, $newWidth, $newHeight, $imageDetails[0], $imageDetails[1]);
        
        if (!$success)
        {
            imagedestroy(newImage);
            imagedestroy(originalResource);
            return "";
        }
        $pathInfo = pathinfo($filePath);
        $newFilePath = $destinationPath."/".$pathInfo['filename'];
        if ($imageDetails[2] == IMAGETYPE_JPEG) 
        {
            $newFilePath .= ".jpg";
            $success = imagejpeg($newImage, $newFilePath, 100);
        } 
        elseif ($imageDetails[2] == IMAGETYPE_PNG) 
        {
            $newFilePath .= ".png";
            $success = imagepng($newImage, $newFilePath, 0);
        } 
        elseif ($imageDetails[2] == IMAGETYPE_GIF) 
        {
            $newFilePath .= ".gif";
            $success = imagegif($newImage, $newFilePath);
        }
        
        imagedestroy($newImage);
        imagedestroy($originalResource);
        
        if (!$success)
        {
            return "";
        }
        else
        {
            //return newFilePath;
        }
    }

    function rotateImage($filePath, $degrees)
    {
        $imageDetails = getimagesize($filePath);
        
        $originalResource = null;
        if ($imageDetails[2] == IMAGETYPE_JPEG) 
        {
            $originalResource = imagecreatefromjpeg($filePath);
        } 
        elseif ($imageDetails[2] == IMAGETYPE_PNG) 
        {
            $originalResource = imagecreatefrompng($filePath);
        } 
        elseif ($imageDetails[2] == IMAGETYPE_GIF) 
        {
            $originalResource = imagecreatefromgif($filePath);
        }
        
        $rotatedResource = imagerotate($originalResource, $degrees, 0);
        
        if ($imageDetails[2] == IMAGETYPE_JPEG) 
        {
            $success = imagejpeg($rotatedResource, $filePath, 100);
        } 
        elseif ($imageDetails[2] == IMAGETYPE_PNG) 
        {
            $success = imagepng($rotatedResource, $filePath, 0);
        } 
        elseif ($imageDetails[2] == IMAGETYPE_GIF) 
        {
            $success = imagegif($rotatedResource, $filePath);
        }
        
        imagedestroy($rotatedResource);
        imagedestroy($originalResource);
    }
?>