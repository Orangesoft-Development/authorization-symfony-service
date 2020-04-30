<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class StreamFile extends Constraint
{
    const EMPTY_ERROR = '5d743385-9775-4aa5-8ff5-495fb1e60137';
    const TOO_LARGE_ERROR = 'df8637af-d466-48c6-a59d-e7126250a654';
    const INVALID_MIME_TYPE_ERROR = '744f00bc-4389-4c74-92de-9a43cde55534';

    /**
     * @var string
     */
    public $maxSizeMessage = 'The file is too large ({{ size }} {{ suffix }}). Allowed maximum size is {{ limit }} {{ suffix }}.';

    /**
     * @var string
     */
    public $mimeTypesMessage = 'The mime type of the file is invalid ({{ type }}). Allowed mime types are {{ types }}.';

    /**
     * @var string
     */
    public $disallowEmptyMessage = 'An empty file is not allowed.';

    /**
     * @var array
     */
    protected $mimeTypes = [];

    /**
     * @var int
     */
    protected $maxSize;

    /**
     * StreamFile constructor.
     *
     * @param array|null $options
     */
    public function __construct(?array $options = null)
    {
        parent::__construct($options);

        if (null !== $this->maxSize) {
            $this->normalizeMaxSize($this->maxSize);
        }
    }

    /**
     * @return array
     */
    public function getMimeTypes(): array
    {
        return $this->mimeTypes;
    }

    /**
     * @return int
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
     * @param string $maxSize
     */
    public function setMaxSize(string $maxSize): void
    {
        $this->normalizeMaxSize($maxSize);
    }

    /**
     * @param string $maxSize
     */
    private function normalizeMaxSize(string $maxSize): void
    {
        $factors = [
            'k' => 1000,
            'ki' => 1 << 10,
            'm' => 1000 * 1000,
            'mi' => 1 << 20,
            'g' => 1000 * 1000 * 1000,
            'gi' => 1 << 30,
        ];

        if (ctype_digit((string) $maxSize)) {
            $this->maxSize = (int) $maxSize;
        } else {
            $pattern = '/^(\d++)('.implode('|', array_keys($factors)).')$/i';
            if (preg_match($pattern, $maxSize, $matches)) {
                $this->maxSize = $matches[1] * $factors[$unit = strtolower($matches[2])];
            } else {
                throw new ConstraintDefinitionException(
                    sprintf('"%s" is not a valid maximum size', $this->maxSize)
                );
            }
        }
    }

    /**
     * @return string
     */
    public function validatedBy(): string
    {
        return StreamFileValidator::class;
    }
}
