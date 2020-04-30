<?php

namespace App\Validator;

use App\Storage\StreamedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use function strlen;

class StreamFileValidator extends ConstraintValidator
{
    const KB_BYTES = 1000;
    const MB_BYTES = 1000000;

    private static $suffices = [
        1 => 'bytes',
        self::KB_BYTES => 'kB',
        self::MB_BYTES => 'MB',
    ];

    /**
     * @param StreamedFile|mixed $value
     * @param StreamFile|Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$this->validateMinSize($value, $constraint)) {
            return;
        }

        if ($constraint->getMaxSize() && !$this->validateMaxSize($value, $constraint)) {
            return;
        }

        if ($constraint->getMimeTypes() && !$this->validateMimeType($value, $constraint)) {
            return;
        }
    }

    /**
     * @param StreamedFile $value
     * @param StreamFile $constraint
     *
     * @return bool
     */
    public function validateMinSize(StreamedFile $value, StreamFile $constraint): bool
    {
        if (0 === $value->getSize()) {
            $this->context
                ->buildViolation($constraint->disallowEmptyMessage)
                ->setCode(StreamFile::EMPTY_ERROR)
                ->addViolation()
            ;

            return false;
        }

        return true;
    }

    /**
     * @param StreamedFile $value
     * @param StreamFile $constraint
     *
     * @return bool
     */
    public function validateMaxSize(StreamedFile $value, StreamFile $constraint): bool
    {
        $limitInBytes = $constraint->getMaxSize();

        if ($value->getSize() > $limitInBytes) {
            [$sizeAsString, $limitAsString, $suffix] = $this->factorizeSizes($value->getSize(), $limitInBytes);
            $this->context
                ->buildViolation($constraint->maxSizeMessage)
                ->setParameter('{{ size }}', $sizeAsString)
                ->setParameter('{{ limit }}', $limitAsString)
                ->setParameter('{{ suffix }}', $suffix)
                ->setCode(StreamFile::TOO_LARGE_ERROR)
                ->addViolation()
            ;

            return false;
        }

        return true;
    }

    /**
     * @param StreamedFile $value
     * @param StreamFile $constraint
     *
     * @return bool
     */
    public function validateMimeType(StreamedFile $value, StreamFile $constraint): bool
    {
        $mime = $value->getMimeType();

        foreach ($constraint->getMimeTypes() as $mimeType) {
            if ($mimeType === $mime) {
                return true;
            }

            if ($discrete = strstr($mimeType, '/*', true)) {
                if (strstr($mime, '/', true) === $discrete) {
                    return true;
                }
            }
        }

        $this->context
            ->buildViolation($constraint->mimeTypesMessage)
            ->setParameter('{{ type }}', $this->formatValue($mime))
            ->setParameter('{{ types }}', $this->formatValues($constraint->getMimeTypes()))
            ->setCode(StreamFile::INVALID_MIME_TYPE_ERROR)
            ->addViolation()
        ;

        return false;
    }

    /**
     * @param string $double
     * @param int $numberOfDecimals
     *
     * @return bool
     */
    private static function moreDecimalsThan(string $double, int $numberOfDecimals): bool
    {
        return strlen((string) $double) > strlen(round($double, $numberOfDecimals));
    }

    /**
     * Convert the limit to the smallest possible number
     * (i.e. try "MB", then "kB", then "bytes").
     *
     * @param int $size
     * @param int $limit
     *
     * @return array
     */
    private function factorizeSizes(int $size, int $limit): array
    {
        $coef = self::MB_BYTES;
        $coefFactor = self::KB_BYTES;

        $limitAsString = (string) ($limit / $coef);

        // Restrict the limit to 2 decimals (without rounding! we
        // need the precise value)
        while (self::moreDecimalsThan($limitAsString, 2)) {
            $coef /= $coefFactor;
            $limitAsString = (string) ($limit / $coef);
        }

        // Convert size to the same measure, but round to 2 decimals
        $sizeAsString = (string) round($size / $coef, 2);

        // If the size and limit produce the same string output
        // (due to rounding), reduce the coefficient
        while ($sizeAsString === $limitAsString) {
            $coef /= $coefFactor;
            $limitAsString = (string) ($limit / $coef);
            $sizeAsString = (string) round($size / $coef, 2);
        }

        return [$sizeAsString, $limitAsString, self::$suffices[$coef]];
    }
}
