<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Common\Annotations;

/**
 * File cache reader for annotations.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class FileCacheReader implements Reader {
	/**
	 * @var Reader
	 */
	private $reader;
	private $dir;
	private $debug;
	private $loadedAnnotations = array();

	public function __construct(Reader $reader, $cacheDir, $debug = false) {
		$this->reader = $reader;
		if (!is_dir($cacheDir)) {
			throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $cacheDir));
		}
		if (!is_writable($cacheDir)) {
			throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $cacheDir));
		}

		$this->dir = rtrim($cacheDir, '\\/');
		$this->debug = $debug;
	}

	public function getClassAnnotations(\ReflectionClass $class) {
		$key = $class->getName();

		if (isset($this->loadedAnnotations[$key])) {
			return $this->loadedAnnotations[$key];
		}

		$path = $this->dir . '/' . strtr($key, '\\', '-') . '.cache.php';
		if (!file_exists($path)) {
			$annot = $this->reader->getClassAnnotations($class);
			$this->saveCacheFile($path, $annot);
			return $this->loadedAnnotations[$key] = $annot;
		}

		if ($this->debug && (false !== $filename = $class->getFilename()) && filemtime($path) < filemtime($filename)) {
			@unlink($path);

			$annot = $this->reader->getClassAnnotations($class);
			$this->saveCacheFile($path, $annot);
			return $this->loadedAnnotations[$key] = $annot;
		}

		return $this->loadedAnnotations[$key] = include $path;
	}

	public function getPropertyAnnotations(\ReflectionProperty $property) {
		$class = $property->getDeclaringClass();
		$key = $class->getName() . '$' . $property->getName();

		if (isset($this->loadedAnnotations[$key])) {
			return $this->loadedAnnotations[$key];
		}

		$path = $this->dir . '/' . strtr($key, '\\', '-') . '.cache.php';
		if (!file_exists($path)) {
			$annot = $this->reader->getPropertyAnnotations($property);
			$this->saveCacheFile($path, $annot);
			return $this->loadedAnnotations[$key] = $annot;
		}

		if ($this->debug && (false !== $filename = $class->getFilename()) && filemtime($path) < filemtime($filename)) {
			unlink($path);

			$annot = $this->reader->getPropertyAnnotations($property);
			$this->saveCacheFile($path, $annot);
			return $this->loadedAnnotations[$key] = $annot;
		}

		return $this->loadedAnnotations[$key] = include $path;
	}

	public function getMethodAnnotations(\ReflectionMethod $method) {
		$class = $method->getDeclaringClass();
		$key = $class->getName() . '#' . $method->getName();

		if (isset($this->loadedAnnotations[$key])) {
			return $this->loadedAnnotations[$key];
		}

		$path = $this->dir . '/' . strtr($key, '\\', '-') . '.cache.php';
		if (!file_exists($path)) {
			$annot = $this->reader->getMethodAnnotations($method);
			$this->saveCacheFile($path, $annot);
			return $this->loadedAnnotations[$key] = $annot;
		}

		if ($this->debug && (false !== $filename = $class->getFilename()) && filemtime($path) < filemtime($filename)) {
			unlink($path);

			$annot = $this->reader->getMethodAnnotations($method);
			$this->saveCacheFile($path, $annot);
			return $this->loadedAnnotations[$key] = $annot;
		}

		return $this->loadedAnnotations[$key] = include $path;
	}

	private function saveCacheFile($path, $data) {
		file_put_contents($path, '<?php return unserialize(' . var_export(serialize($data), true) . ');');
	}

	/**
	 * Gets a class annotation.
	 *
	 * @param ReflectionClass $class The ReflectionClass of the class from which
	 *                               the class annotations should be read.
	 * @param string $annotationName The name of the annotation.
	 * @return The Annotation or NULL, if the requested annotation does not exist.
	 */
	public function getClassAnnotation(\ReflectionClass $class, $annotationName) {
		$annotations = $this->getClassAnnotations($class);

		foreach ($annotations as $annotation) {
			if ($annotation instanceof $annotationName) {
				return $annotation;
			}
		}

		return null;
	}

	/**
	 * Gets a method annotation.
	 *
	 * @param ReflectionMethod $method
	 * @param string $annotationName The name of the annotation.
	 * @return The Annotation or NULL, if the requested annotation does not exist.
	 */
	public function getMethodAnnotation(\ReflectionMethod $method, $annotationName) {
		$annotations = $this->getMethodAnnotations($method);

		foreach ($annotations as $annotation) {
			if ($annotation instanceof $annotationName) {
				return $annotation;
			}
		}

		return null;
	}

	/**
	 * Gets a property annotation.
	 *
	 * @param ReflectionProperty $property
	 * @param string $annotationName The name of the annotation.
	 * @return The Annotation or NULL, if the requested annotation does not exist.
	 */
	public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName) {
		$annotations = $this->getPropertyAnnotations($property);

		foreach ($annotations as $annotation) {
			if ($annotation instanceof $annotationName) {
				return $annotation;
			}
		}

		return null;
	}

	public function clearLoadedAnnotations() {
		$this->loadedAnnotations = array();
	}
}
