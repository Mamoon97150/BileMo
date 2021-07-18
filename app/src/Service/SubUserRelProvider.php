<?php


namespace App\Service;


use Hateoas\Configuration\Embedded;
use Hateoas\Configuration\Exclusion;
use Hateoas\Configuration\Relation;
use Hateoas\Configuration\Route;
use JMS\Serializer\Expression\CompilableExpressionEvaluatorInterface;

class SubUserRelProvider
{
    private CompilableExpressionEvaluatorInterface $evaluator;

    /**
     * SubUserRelProvider constructor.
     * @param CompilableExpressionEvaluatorInterface $evaluator
     */
    public function __construct(CompilableExpressionEvaluatorInterface $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    public function getRelations()
    {
        return array(
            new Relation(
              'self',
              new Route(
                  'api_sub_item',
                  ['id' => $this->evaluator->parse('object.getId()', ['object'])],
                  true
              ),
              new Embedded(
                  $this->evaluator->parse("object.getUsers()", ['object']),
                  new Exclusion([
                      'groups' => ['sub_details'],
                      null,
                      null,
                      'maxDepth' => 1
                  ])
              ),
              null,
              new Exclusion([
                  'groups' => ['sub_details'],
                  null,
                  null,
                  'maxDepth' => 1
              ])
            ),
            new Relation(
                'update',
                new Route(
                    'api_sub_update',
                    ['id' => $this->evaluator->parse('object.getId()', ['object'])],
                    true
                ),
                null,
                null,
                new Exclusion([
                    'groups' => ['sub_details'],
                    null,
                    null,
                    'maxDepth' => 1
                ])
            ),
            new Relation(
                'delete',
                new Route(
                    'api_sub_delete',
                    ['id' => $this->evaluator->parse('object.getId()', ['object'])],
                    true
                ),
                null,
                null,
                new Exclusion([
                    null,
                    null,
                    null,
                    'maxDepth' => 1
                ])
            )
        );
    }

}