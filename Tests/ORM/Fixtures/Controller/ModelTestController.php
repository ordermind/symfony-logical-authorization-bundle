<?php



//   /**
//     * @Route("/count-entities-roleauthor", name="test_count_entities_roleauthor")
//     * @Method({"GET"})
//     */
//   public function countEntitiesRoleAuthorAction(Request $request) {
//     $operations = $this->get('test_entity_operations');
//     $operations->setRepositoryManager($this->get('repository_manager.test_entity_roleauthor_annotation'));
//     $entities = $operations->findTestEntities();
//     return new Response(count($entities));
//   }
//
//   /**
//     * @Route("/count-entities-hasaccount", name="test_count_entities_hasaccount")
//     * @Method({"GET"})
//     */
//   public function countEntitiesHasAccountAction(Request $request) {
//     $operations = $this->get('test_entity_operations');
//     $operations->setRepositoryManager($this->get('repository_manager.test_entity_hasaccount_annotation'));
//     $entities = $operations->findTestEntities();
//     return new Response(count($entities));
//   }
//
//   /**
//     * @Route("/count-entities-nobypass", name="test_count_entities_nobypass")
//     * @Method({"GET"})
//     */
//   public function countEntitiesNoBypassAction(Request $request) {
//     $operations = $this->get('test_entity_operations');
//     $operations->setRepositoryManager($this->get('repository_manager.test_entity_nobypass_annotation'));
//     $entities = $operations->findTestEntities();
//     return new Response(count($entities));
//   }
//
//
//
//   /**
//     * @Route("/read-field-1-roleauthor", name="test_read_field1_roleauthor")
//     * @Method({"GET"})
//     */
//   public function readField1RoleAuthorAction(Request $request) {
//     $operations = $this->get('test_entity_operations');
//     $operations->setRepositoryManager($this->get('repository_manager.test_entity_roleauthor_annotation'));
//     $entities = $operations->findTestEntities();
//     if($entities) {
//       $modelManager = array_shift($entities);
//       return new Response($modelManager->getField1());
//     }
//     throw new AccessDeniedHttpException('Permission denied.');
//   }
//
//   /**
//     * @Route("/read-field-1-hasaccount", name="test_read_field1_hasaccount")
//     * @Method({"GET"})
//     */
//   public function readField1HasAccountAction(Request $request) {
//     $operations = $this->get('test_entity_operations');
//     $operations->setRepositoryManager($this->get('repository_manager.test_entity_hasaccount_annotation'));
//     $entities = $operations->findTestEntities();
//     if($entities) {
//       $modelManager = array_shift($entities);
//       return new Response($modelManager->getField1());
//     }
//     throw new AccessDeniedHttpException('Permission denied.');
//   }
//
//   /**
//     * @Route("/read-field-1-nobypass", name="test_read_field1_nobypass")
//     * @Method({"GET"})
//     */
//   public function readField1NoBypassAction(Request $request) {
//     $operations = $this->get('test_entity_operations');
//     $operations->setRepositoryManager($this->get('repository_manager.test_entity_nobypass_annotation'));
//     $entities = $operations->findTestEntities();
//     if($entities) {
//       $modelManager = array_shift($entities);
//       return new Response($modelManager->getField1());
//     }
//     throw new AccessDeniedHttpException('Permission denied.');
//   }
//
//   /**
//     * @Route("/update-entity-roleauthor", name="test_update_entity_roleauthor")
//     * @Method({"POST"})
//     */
//   public function updateEntityRoleAuthorAction(Request $request) {
//     $operations = $this->get('test_entity_operations');
//     $operations->setRepositoryManager($this->get('repository_manager.test_entity_roleauthor_annotation'));
//     $entities = $operations->findTestEntities(true);
//     if($entities) {
//       $entity = array_shift($entities);
//       $operations->updateTestEntity($entity);
//     }
//     return new Response('');
//   }
//
//   /**
//     * @Route("/update-entity-hasaccount", name="test_update_entity_hasaccount")
//     * @Method({"POST"})
//     */
//   public function updateEntityHasAccountAction(Request $request) {
//     $operations = $this->get('test_entity_operations');
//     $operations->setRepositoryManager($this->get('repository_manager.test_entity_hasaccount_annotation'));
//     $entities = $operations->findTestEntities(true);
//     if($entities) {
//       $entity = array_shift($entities);
//       $operations->updateTestEntity($entity);
//     }
//     return new Response('');
//   }
//
  /**
    * @Route("/update-entity-nobypass", name="test_update_entity_nobypass")
    * @Method({"POST"})
    */
  public function updateEntityNoBypassAction(Request $request) {
    $operations = $this->get('test_entity_operations');
    $operations->setRepositoryManager($this->get('repository_manager.test_entity_nobypass_annotation'));
    $entities = $operations->findTestEntities(true);
    if($entities) {
      $entity = array_shift($entities);
      $operations->updateTestEntity($entity);
    }
    return new Response('');
  }
