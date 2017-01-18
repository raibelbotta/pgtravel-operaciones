<?php

namespace AppBundle\Lib\Excel;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Liuggio\ExcelBundle\Factory;

/**
 * Description of ExportableBook
 * 
 * @author Raibel Botta <raibelbotta@gmail.com>
 */
abstract class ExportableBook
{
    /**
     * @var array
     */
    protected $options;
    
    /**
     * @var Factory
     */
    protected $phpexcel;
    
    public function __construct(array $options = array())
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        
        $this->options = $resolver->resolve($options);
        
        $this->phpexcel = $this->options['phpexcel'];
    }
    
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
                ->setRequired('phpexcel')
                ->setAllowedTypes('phpexcel', 'Liuggio\\ExcelBundle\\Factory')
                ;
    }
}
