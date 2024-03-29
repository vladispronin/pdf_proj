<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

require_once '../aws/credentials.php';

class PdfController extends AbstractController
{
    #[Route('/pdf', name: 'app_pdf', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        $s3Client = new S3Client([
            'version' => 'latest',
            'endpoint' => STORAGE_ENDPOINT,
            'credentials' => [
                'key'    => ACCESS_KEY,
                'secret' => SECRET_KEY,
            ],
            'region' => 'ru-central1',
        ]);
         
        $buckets = $s3Client->listBuckets();
        $bucket = $buckets['Buckets'][0]['Name'];

        $file = array_values($_FILES)[0];
        if ($file['type'] === 'application/pdf') {
            try {
                $result = $s3Client->putObject([
                    'Bucket' => $bucket,
                    'Key' => $file['name'],
                    'SourceFile' => $file['tmp_name'],
                ]);
                return $this->json(
                    [
                        'message' => 'File uploaded successfully',
                        'path' => $result->get('ObjectURL')
                    ]
                );
            } catch (S3Exception $e) {
                return $this->json(
                    [
                        'error' => "There was an error uploading the file",
                        'message' => $e->getMessage()
                    ]
                );
            }
        } else {
            return $this->json(
                [
                    'message' => "Should upload PDF file",
                ]
            );
        }
    }
}
