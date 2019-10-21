<?php





router('/assets/(.*)', function ($path) {
  
    
    $assetsPaths = [
        ROOT_DIR . '/public/assets/' . $path,
        ROOT_DIR . '/vendor/twbs/bootstrap/dist/' . $path,
        ROOT_DIR . '/vendor/fortawesome/font-awesome/' . $path,
        ROOT_DIR . '/vendor/components/jquery/' . str_replace('js/', '', $path),
       
    ];
    $filePath = null;
    foreach($assetsPaths as $assetsPath){
        $filePath = realpath($assetsPath);
  
        if($filePath) break;
    }

  
    if (!$filePath) {
        return event(EVENT_404,[ '/assets/' . $path]);
    }
    $mimeType = mime_content_type($filePath);
    if ($mimeType === 'text/plain'  || $mimeType === 'text/x-asm') {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypeForExtensions = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'svg'=>'image/svg+xml'
        ];
        if(isset($mimeTypeForExtensions[$extension])){
            $mimeType = $mimeTypeForExtensions[$extension];
        }

    }
 
    header('Content-Type:' . $mimeType);
    ob_end_clean();
    return readfile($filePath);
});

