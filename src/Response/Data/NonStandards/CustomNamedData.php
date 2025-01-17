<?php
declare(strict_types=1);

namespace ArrayAccess\RdapClient\Response\Data\NonStandards;

use ArrayAccess\RdapClient\Interfaces\ResponseData\RdapResponseDataInterface;
use ArrayAccess\RdapClient\Interfaces\ResponseData\RdapResponseDataNamedInterface;
use ArrayAccess\RdapClient\Response\Traits\AllowedKeyDataTraits;
use function is_array;

class CustomNamedData implements RdapResponseDataInterface, RdapResponseDataNamedInterface
{
    use AllowedKeyDataTraits;

    /**
     * @var mixed $values
     */
    protected mixed $values;

    /**
     * @inheritDoc
     * @return false
     */
    public function rootOnly() : bool
    {
        return false;
    }

    /**
     * @var string $name
     */
    protected string $name;

    /**
     * @param scalar $name
     * @param mixed $data
     */
    public function __construct(float|bool|int|string $name, mixed $data)
    {
        $this->name = (string) $name;
        $this->values = $data;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValues() : mixed
    {
        return $this->values;
    }

    /**
     * @return mixed
     */
    public function jsonSerialize() : mixed
    {
        return $this->values;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getPlainData(): array
    {
        /** @noinspection DuplicatedCode */
        if (is_array($this->values)) {
            $values = [];
            foreach ($this->values as $key => $value) {
                if ($value instanceof RdapResponseDataInterface) {
                    $name = $value->getName();
                    if ($name === null) {
                        $values[] = $value->getPlainData();
                        continue;
                    }
                    $values[$name] = $value->getPlainData();
                    continue;
                }
                $values[$key] = $value;
            }
            return $values;
        }
        return $this->values instanceof RdapResponseDataInterface
            ? $this->values->getPlainData()
            : $this->values;
    }
}
