<?php

require_once dirname(__FILE__).'/../lib/surveyGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/surveyGeneratorHelper.class.php';

/**
 * survey actions.
 *
 * @package    e-venement
 * @subpackage survey
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class surveyActions extends autoSurveyActions
{
  public function executeAddQuery(sfWebRequest $request)
  {
    $this->redirect('query/new?survey-id='.$request->getParameter('id'));
  }
  
  public function executeShow(sfWebRequest $request)
  {
    parent::executeShow($request);
    $this->form = new SurveyPublicForm($this->survey);
  }
  
  public function executeCommit(sfWebRequest $request)
  {
    $params = $request->getParameter('survey');
    $request->setParameter('id', $params['id']);
    parent::executeShow($request);
    
    // add the lang param, adjusted to the user's culture
    foreach ( $params['answers'] as $id => $param )
    if ( is_array($param) && !(isset($params['answers'][$id]['lang']) && $params['answers'][$id]['lang']) )
      $params['answers'][$id]['lang'] = $this->getUser()->getCulture();
    
    // add the link to the user's contact
    if ( $this->getUser()->getContact() )
      $params['answers']['contact_id'] = $this->getUser()->getContact()->id;
    
    $this->form = new SurveyPublicForm($this->survey);
    $this->form->bind($params);
    if ( $this->form->isValid() )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('success', __('Submission recorded.'));
      
      $object = $this->form->save();
      $this->redirect('survey/show?id='.$this->survey->id);
    }
    
    $this->setTemplate('show');
  }
}