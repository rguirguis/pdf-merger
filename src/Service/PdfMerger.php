<?php

namespace Drupal\pdf_merger\Service;

use Drupal\pdf_merger\Helper\PdfHelperInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Use it to combine multiple pdfs in one pdf file
 *
 * @author Yousab
 */
class PdfMerger implements PdfHelperInterface {

  /**
   * @var ConfigFactoryInterface 
   */
  protected $configFactory;

  /**
   * @var LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * @var FileSystemInterface 
   */
  protected $fileSystem;

  /**
   *
   * @var ImmutableConfig 
   */
  protected $pdfMergerConfig;

  public function __construct(ConfigFactoryInterface $configFactory, LoggerChannelFactoryInterface $loggerFactory, FileSystemInterface $fileSystem) {
    $this->configFactory = $configFactory;
    $this->loggerFactory = $loggerFactory;
    $this->fileSystem = $fileSystem;
    $this->pdfMergerConfig = $this->configFactory->get('pdf_merger.settings');
  }

  /**
   * Check whether or pdftk binary file existed
   * @param string $path
   * @return FileNotFoundException|boolean
   */
  public function isBinaryExisted($path) {
    if (!file_exists($path)) {
      return new FileNotFoundException($path);
    }

    return TRUE;
  }

  /**
   * Check if destination directory is writable.
   * @param string $destination
   * @return boolean|\Exception
   */
  public function isDirectoryWritable($destination) {
    if (!file_prepare_directory($destination, FILE_CREATE_DIRECTORY)) {
      $this->loggerFactory->get('pdf_merger')->error('The destonation directory %directory could not be created or is not accessible.', ['%directory' => $destination]);
      return new \Exception('Directory could not be created or is not accessible.');
    }

    return TRUE;
  }

  /**
   * Use Pdftk to merge multiple files
   * @param array $files
   * @param string $destination
   * @param string $filename
   * @return boolean
   */
  public function merge(array $files, $destination, $filename) {
    $pdfBinary = $this->pdfMergerConfig->get('exec');

    if (!$this->isBinaryExisted($pdfBinary) || !$this->isDirectoryWritable($destination)) {
      return;
    }

    $destination = escapeshellarg($this->fileSystem->realpath($destination . '/' . $filename));
    $files = $this->getFilesRealPaths($files);
    $cmd = $pdfBinary . ' ' . implode(' ', $files) . ' cat output ' . $destination;
    $output = shell_exec($cmd);

    // Check if PDFTK was able to merge pdf files or not.
    if ($output) {
      $this->loggerFactory->get('pdf_merger')->error('PDFTK could not merge the PDF files');
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Get real paths from file URIs
   * @param array $files
   */
  public function getFilesRealPaths(array $fileURIs) {
    $realPaths = [];

    foreach ($fileURIs as $fileURI) {
      $realPaths[] = escapeshellarg($this->fileSystem->realpath($fileURI));
    }

    return $realPaths;
  }

}
