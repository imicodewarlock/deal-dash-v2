<?php

namespace App\Http\Controllers;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Google\Client as GoogleClient;

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

    public function sendFcmNotification(string $fcm_token, $notification = [], $dataSrc = [])
    {
        $projectId = "dealdash-878ba"; # INSERT COPIED PROJECT ID

        $credentials = [
            "type" => "service_account",
            "project_id" => "dealdash-878ba",
            "private_key_id" => "d3f336523acbd398eba8eefb3c7b96320998a791",
            "private_key" => "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDYZP4R540ZL7jc\nPNSDZ1AinGzn38kKWyr29SU24IlVvRjT23u8TQjV5uSjqzW68h+GocwPCsc3ZY0V\n7wSpivER0Z6F/L9D/H5iY/rDpYuJaw37zlrGvp+fUU90zmHcm6sMeMZJouIuRBqL\nzTz033O4eH9C+UoPgk+W6ZaGn92tBYZciaPxWhVOPzps0zOo1rUkxiN2A4/twRYp\nud1x+5+j25hq5nXYRbDJixysFVhPz+dMZkHq2D6Mygm+ICwz2NeZTmtD49H+cGKW\n2Td9mxO4/FCjyOhvsaYD3e1vY/SF4vIHeuSgYHcxX1CaKDkAGAZHAl4vgWmxCGHB\nTXvxwozZAgMBAAECggEAIUUFuwL6spCfv1yq37kWnPun0s6ZPjpeFLIEErfa5Y+5\nLXllQMInRZvGM1OzYxauuihOE1H45w957ZFeCiMOhUrRMJB3Z6B+Xeo5N7NQDMSk\n1b4YPs44BGqf6LmCWkSku7ol4lRkqfBWlH/ti0h/pjEAf++L7259hhpLxk716g/T\nArps1oIqinfkKuBfEvVJu5c5IaeQiqwhdFB2vRxrINVpGcQJUfGh7wa/ZSQJUq7J\nVEexCcGLYwaUBeSg3avz22srqjtYXM+NJAZJJBZtqPLFck1fFAMD+fXH6HNmvIND\nf9aYaHqOytl+CN/XBT0CDPT/uT+tUKS8WKFbVOeR8QKBgQDu+JN2BY6/uXJjMQHu\nzR7WUj1DKgFRhJxuUgdL2ZqaSyVCUEK/LfhRu92aYPxCNFxWt/5LrXzttCfgjHWz\nIB7+fjpZkog+rH5W50KFtYMw/mM3k9z7i7waFpc0L7/rOzN1JhEimUcBe42+8DUe\n5JgKB0GxKxCG/UVbcW3JSFEV4wKBgQDn0JCqFhi/dgv8de9NG234NPzQn7t0hXrv\nLIIDyOB0stee/fOdt1ZRUSQmBK86ucjUfZ2WeDwwT5bnh9RgBKoOk/A3xH0l9v2u\n6R/ctbWrQYxvaxyR5gpHZ7FimuuATMTUTZfj/rDNRwmLGX03c040tD33Jf5JPIAg\n51t7N6XvEwKBgDckz0pVv/oH/hhbj7meRbZpJc/g2osIEdz3Os3K+f0OyBtEUBKz\ntfCObHWaWbuhP4mXTawC7agggW80mlhqWhyZ3jcbNCtaPJErLlOSbiKZISYLDdxS\nA2b1vZCCUEQk1hv7W8rjGdqCu1PLNEbbsyXlRMhwSpEL0rxmMVJYSLXHAoGBAMEn\nUJLYTJCjvMMERXu7IjtRc2Il5hzl55QIQuECbLvwFKe+tFGy5LJm6Lbg6l0FVmhv\nrnIlBwm+F2AeFoBXApeY/uyIxTpv/drTFqBhq9jhijGAT1LmGaR7qxsdOKMz1EGN\n0cTAf/LbgQEtIN+mLQDPOl6HgsTlK2G/RMN7j8CPAoGBAMUhlardzzm2m5LWsbpZ\nPWW+v+kMVb75ICtQwFJ5Hn8gucUVW9yIjI5FhBI1H6yt6TCvYHciif5OA5uWAXlu\nOfe/Xk+i7aVknPqWnFWKtkwVq20be3fMlXglnamnH/cEiksydOTxipmTkurvGf++\nV62kJKcdj6IKhdLZ8ovoWWPH\n-----END PRIVATE KEY-----\n",
            "client_email" => "firebase-adminsdk-1mz2x@dealdash-878ba.iam.gserviceaccount.com",
            "client_id" => "106114390489036464482",
            "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
            "token_uri" => "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
            "client_x509_cert_url" => "https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-1mz2x%40dealdash-878ba.iam.gserviceaccount.com",
            "universe_domain" => "googleapis.com"
        ];
        $client = new GoogleClient();
        $client->setAuthConfig($credentials);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        $access_token = $token['access_token'];

        $headers = [
            "Authorization: Bearer $access_token",
            'Content-Type: application/json'
        ];

        $data = [
            "message" => [
                "token" => $fcm_token,
                "notification" => [
                    "title" => $notification['title'],
                    "body" => $notification['body'],
                    "image" => $notification['image'],
                ],
                "data" => $dataSrc,
            ]
        ];
        $payload = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_VERBOSE, true); // Enable verbose output for debugging
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return $this->sendError($err, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
