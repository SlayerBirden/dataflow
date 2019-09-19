<?php
declare(strict_types=1);

namespace SlayerBirden\DataFlow\Provider;

use SlayerBirden\DataFlow\Data\SimpleBag;
use SlayerBirden\DataFlow\IdentificationTrait;
use SlayerBirden\DataFlow\Provider\Exception\FileDoesNotExist;
use SlayerBirden\DataFlow\Provider\Exception\RowInvalid;
use SlayerBirden\DataFlow\Provider\Exception\HeaderMissing;
use SlayerBirden\DataFlow\ProviderInterface;

class Csv implements ProviderInterface
{
    use IdentificationTrait;

    /**
     * @var string
     */
    private $id;
    /**
     * @var \SplFileObject
     */
    private $file;
    /**
     * @var bool
     */
    private $headerRow;
    /**
     * @var array|null
     */
    private $header;

    /**
     * @param string $id
     * @param \SplFileObject $file
     * @param bool $headerRow
     * @param string[]|null $header
     * @throws FileDoesNotExist
     * @throws HeaderMissing
     */
    public function __construct(string $id, \SplFileObject $file, bool $headerRow = true, ?array $header = null)
    {
        $this->id = $id;
        $this->file = $file;
        if (!$headerRow && empty($header)) {
            throw new HeaderMissing(
                sprintf('You did not provide header for the file %s.', $this->file->getFilename())
            );
        }
        $this->headerRow = $headerRow;
        $this->header = $header;
    }

    /**
     * @inheritdoc
     * @throws RowInvalid
     */
    public function getCask(): \Generator
    {
        $this->file->rewind();
        if ($this->header === null && $this->headerRow) {
            // get header from the 1st row only if header is not explicitly provided
            $this->header = $this->file->current();
        }
        if ($this->headerRow) {
            $this->file->next();
        }

        while ($this->file->valid()) {
            $row = $this->file->current();
            if (count($row) !== count($this->header)) {
                yield new RowInvalid(
                    sprintf(
                        'Invalid row %s for header %s. Column count mismatch.',
                        json_encode($row),
                        json_encode($this->header)
                    )
                );
            } else {
                yield new SimpleBag(array_combine($this->header, $row));
            }
            $this->file->next();
        }
    }

    /**
     * @inheritdoc
     */
    public function getEstimatedSize(): int
    {
        /**
         * @see https://stackoverflow.com/a/43075929/927404
         */
        // attempt to reach max (will reach last line);
        $prevPos = $this->file->key();
        $this->file->seek(PHP_INT_MAX);
        $numberOfLines = $this->file->key();
        if ($this->file->valid()) {
            // this line is not eof
            $numberOfLines += 1;
        }
        if ($this->headerRow) {
            // subtract header row if it's present in the file
            --$numberOfLines;
        }
        // return the file to the previous line
        $this->file->seek($prevPos);
        return $numberOfLines;
    }
}
