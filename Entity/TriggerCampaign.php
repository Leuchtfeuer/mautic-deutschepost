<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use MauticPlugin\MauticTriggerdialogBundle\Validator\AllowedCharacters;
use MauticPlugin\MauticTriggerdialogBundle\Validator\Variable;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as SymfonyClassMetadata;

class TriggerCampaign extends FormEntity
{
    const ALLOWED_TYPES = [
        'boolean' => 'boolean',
        'float' => 'float',
        'integer' => 'integer',
        'string' => 'string',
        'date' => 'date',
        'set' => 'set',
        'image' => 'image',
        'zip' => 'zip',
        'countryCode' => 'countryCode',
        'imageurl' => 'imageurl',
    ];

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $triggerId = 0;

    /**
     * @var int
     */
    private $mailingId = 0;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $startDate;

    /**
     * @var null|\DateTime
     */
    private $endDate;

    /**
     * @var array
     */
    private $variables;

    /**
     * @var string
     */
    private $printNodeId;

    /**
     * @var string
     */
    private $printNodeDescription;

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->id = null;

        parent::__clone();
    }

    /**
     * Campaign constructor.
     */
    public function __construct()
    {
        $this->setStartDate();
    }

    /**
     * {@inheritdoc}
     *
     * @param ClassMetadata $metadata
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('trigger_campaigns')->setCustomRepositoryClass(TriggerCampaignRepository::class);
        $builder->addIdColumns();
        $builder->createField('mailingId', 'string')->columnName('mailing_id')->build();
        $builder->createField('triggerId', 'string')->columnName('trigger_id')->build();
        $builder->createField('startDate', 'datetime')->columnName('start_date')->build();
        $builder->createField('endDate', 'datetime')->columnName('end_date')->nullable()->build();
        $builder->createField('printNodeId', 'string')->columnName('print_node_id')->build();
        $builder->createField('printNodeDescription', 'string')->columnName('print_node_description')->build();
        $builder->createField('variables', 'array')->build();
    }

    /**
     * @param SymfonyClassMetadata $metadata
     */
    public static function loadValidatorMetadata(SymfonyClassMetadata $metadata)
    {
        $metadata->addPropertyConstraints('name', [
            new NotBlank([
                'message' => 'mautic.core.name.required',
            ]),
            new Length([
                'min' => 1,
                'max' => 30,
            ]),
            new AllowedCharacters(),
        ]);

        $metadata->addPropertyConstraints('printNodeId', [
            new Length([
                'min' => 1,
                'max' => 32,
            ]),
        ]);

        $metadata->addPropertyConstraints('printNodeDescription', [
            new Length([
                'min' => 1,
                'max' => 30,
            ]),
            new AllowedCharacters(),
        ]);

        $metadata->addPropertyConstraints('variables', [
            new Variable(),
        ]);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @throws \Exception
     */
    public function setName($name)
    {
        $this->isChanged('name', $name);
        $this->name = $name;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return string
     */
    public function setDescription($description)
    {
        $this->isChanged('description', $description);
        $this->description = $description;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime|null $startDate
     */
    public function setStartDate(\DateTime $startDate = null)
    {
        if ($startDate === null) {
            try {
                $startDate = new \DateTime();
            } catch (\Exception $exception) {
                // Do nothing
            }
        }

        $this->isChanged('startDate', $startDate);
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param null|\DateTime $endDate
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->isChanged('endDate', $endDate);
        $this->endDate = $endDate;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return array
     */
    public function getVariablesAsArray()
    {
        $variableDefDataType = json_decode('[{"id": 10,"label": "string"},{"id": 20,"label": "integer"},{"id": 30,"label": "boolean"},{"id": 40,"label": "date"},{"id": 50,"label": "image"},{"id": 60,"label": "imageurl"},{"id": 70,"label": "float"},{"id": 80,"label": "zip"},{"id": 90,"label": "countryCode"}]', true);
        $variables = [];

        foreach ($this->variables as $variable) {
            $type_def = '';
            foreach ($variableDefDataType as $type) {
                if ($type['label'] === $variable['variable']) {
                    $type_def = $type['id'];
                }
            }
            $variables[] = [
                'label' => $variable['field'],
                'sortOrder' => 0,
                'dataTypeId' => $type_def,
            ];
        }

        return $variables;
    }

    /**
     * @param array $variables
     */
    public function setVariables($variables)
    {
        $this->isChanged('variables', $variables);
        $this->variables = $variables;
    }

    /**
     * @return string
     */
    public function getPrintNodeId()
    {
        return $this->printNodeId;
    }

    /**
     * @param string $printNodeId
     */
    public function setPrintNodeId($printNodeId)
    {
        $this->isChanged('printNodeId', $printNodeId);
        $this->printNodeId = $printNodeId;
    }

    /**
     * @return string
     */
    public function getPrintNodeDescription()
    {
        return $this->printNodeDescription;
    }

    /**
     * @param string $printNodeDescription
     *
     * @throws \Exception
     */
    public function setPrintNodeDescription($printNodeDescription)
    {
        $this->isChanged('printNodeDescription', $printNodeDescription);
        $this->printNodeDescription = $printNodeDescription;
    }

    /**
     * @return int
     */
    public function getTriggerId(): int
    {
        return $this->triggerId;
    }

    /**
     * @param int $triggerId
     */
    public function setTriggerId(int $triggerId): void
    {
        $this->triggerId = $triggerId;
    }

    /**
     * @return int
     */
    public function getMailingId(): int
    {
        return $this->mailingId;
    }

    /**
     * @param int $mailingId
     */
    public function setMailingId(int $mailingId): void
    {
        $this->mailingId = $mailingId;
    }
}
