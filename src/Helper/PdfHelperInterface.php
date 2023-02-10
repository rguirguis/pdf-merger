<?php

namespace Drupal\pdf_merger\Helper;

/**
 * Define interface for pdf merger
 * @author Yousab
 */
interface PdfHelperInterface {
  
  /**
   * Check weather if pdftk binaries existed or not
   * @param string $path
   * @return mixed
   */
  public function isBinaryExisted($path);
  
  /**
   * Merge multiple pdf files together with pdftk
   * @param array $files
   * @param string $destination
   * @param string $filename
   * @return mixed
   */
  public function merge(array $files, $destination, $filename);
}
