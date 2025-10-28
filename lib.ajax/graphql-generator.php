<?php

use AppBuilder\GraphQLGenerator;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$request = file_get_contents('php://input');
$schema = json_decode($request, true);

$withFrontend = isset($schema['withFrontend']) && ($schema['withFrontend'] == 'true' || $schema['withFrontend'] == '1' || $schema['withFrontend'] === true) ? true : false;

try {
    
    if($withFrontend)
    {
        $generator = new GraphQLGenerator($schema);

        // Create ZIP file
        $zip = new ZipArchive();
        // Create a temporary file for the ZIP
        $zipFilePath = tempnam(sys_get_temp_dir(), 'graphql_');
        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Could not create ZIP file.");
        }

        // Add generated code file
        $zip->addFromString('graphql.php', $generator->generate());
        // Add manual content file
        $zip->addFromString('manual.md', $generator->generateManual());
        
        // Add frontend files
        $zip->addFromString('frontend-config.json', $generator->generateFrontendConfigJson());
        $zip->addFromString('lang-en.json', $generator->generateFrontendLanguageJson());
        $zip->addFromString('index.html', $generator->generateFrontendHtml());
        $zip->addFromString('style.css', $generator->generateFrontendCss());
        $zip->addFromString('app.js', $generator->generateFrontendJs());

        $zip->close();
        // Send the ZIP file as a download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="graphql.zip"');
        header('Content-Length: ' . filesize($zipFilePath));
        readfile($zipFilePath);
        // Delete the temporary file
        unlink($zipFilePath);
    }
    else
    {
        $generator = new GraphQLGenerator($schema);
        $generatedCode = $generator->generate();
        $manualContent = $generator->generateManual();

        // Create ZIP file
        $zip = new ZipArchive();
        // Create a temporary file for the ZIP
        $zipFilePath = tempnam(sys_get_temp_dir(), 'graphql_');
        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            throw new Exception("Could not create ZIP file.");
        }

        // Add generated code file
        $zip->addFromString('graphql.php', $generatedCode);
        // Add manual content file
        $zip->addFromString('manual.md', $manualContent);

        $zip->close();
        // Send the ZIP file as a download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="graphql.zip"');
        header('Content-Length: ' . filesize($zipFilePath));
        readfile($zipFilePath);
        // Delete the temporary file
        unlink($zipFilePath);
    }
    header("Content-Type: application/json");
    echo json_encode(['success' => true, 'message' => 'GraphQL code generated successfully.']);
    exit();
    
} catch (Exception $e) {
    header("Content-Type: application/json");
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}