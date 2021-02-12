<?php

namespace Yildirim\Routing;

use Yildirim\Routing\Exceptions\ResponseException;

/**
 * Basic Response Class
 *
 *
 */
class FileResponse extends BaseResponseObject
{

    /**
     * filename
     *
     * @var mixed
     */
    public $filename;

    /**
     * __construct
     *
     * @param  mixed $filepath
     * @param  mixed $filename
     * @return void
     */
    public function __construct($filepath, $filename = null)
    {
        if (!@file_exists($filepath)) {
            throw new ResponseException('File not found [ ' . $filepath . ' ]');
        }

        $this->buildResponse($filepath, $filename);
    }

    /**
     * buildResponse
     *
     * @param  mixed $filepath
     * @param  mixed $filename
     * @return void
     */
    private function buildResponse($filepath, $filename)
    {
        //get file contents.
        $this->body = file_get_contents($filepath);

        //get mime type.
        $mimeType = mime_content_type($filepath);

        $filename = $filename ? $filename : basename($filepath);
        
        $this->filename = $filename;

        $this->setHeaders([
            'Content-Type' => $mimeType,
            'Content-Length' => filesize($filepath),
            'Content-Disposition' => 'inline; filename=' . $filename,
            'Content-Transfer-Encoding' => 'binary',
        ]);
    }

    /**
     * download
     *
     * @return void
     */
    public function download()
    {
        return $this->setHeader('Content-Disposition', 'attachment; filename=' . $this->filename);
    }

}
