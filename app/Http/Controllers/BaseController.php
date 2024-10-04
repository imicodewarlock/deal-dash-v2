<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends Controller
{
    /**
     * Return detailed successful response
     *
     * Undocumented function long description
     *
     * @param mixed $message Description
     * @param array $data
     * @param mixed $code
     * @param array $meta
     * @param array $links
     * @param array $errors
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendSuccess($message, $data = [], $code = Response::HTTP_OK, $meta = [], $links = [], $errors = [])
    {
        # code...
        $response = [
            'success' => true,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
            'meta' => $meta,
            'links' => $links,
        ];
        
        return response()->json($response, $code);
    }
    
    /**
     * Return a detailed error message
     *
     * Undocumented function long description
     *
     * @param String $message Description
     * @param mixed $code
     * @param array $errors
     * @param array $data
     * @param array $meta
     * @param array $links
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendError($message, $code = Response::HTTP_NOT_FOUND, $errors = [], $data = [], $meta = [], $links = [])
    {
        # code...
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
            'meta' => $meta,
            'links' => $links,
        ];
        
        return response()->json($response, $code);
    }
    
    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param \Illuminate\Http\UploadedFile $file Description
     * @param string $path Description
     * 
     * @return string|null
     */
    protected function getUploadedFileUrl(UploadedFile $file, string $path)
    {
        // Generate a unique filename to avoid conflicts
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        $uploadedFilePath = Storage::putFileAs($path, $file, $fileName);

        return $uploadedFilePath ? Storage::url($uploadedFilePath) : null;
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * 
     * @return void
     */
    public function removeOldFile(string $url)
    {
        // Extract the S3 path from the file URL
        $oldFilePath = parse_url($url, PHP_URL_PATH);

        // Remove leading slash
        $oldFilePath = ltrim($oldFilePath, '/');

        Storage::delete($oldFilePath);
    }
}
