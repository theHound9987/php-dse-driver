#include "php_dse.h"
#include "php_dse_types.h"

zend_class_entry *dse_graph_path_ce = NULL;

PHP_METHOD(DseGraphPath, __construct)
{
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_none, 0, ZEND_RETURN_VALUE, 0)
ZEND_END_ARG_INFO()

static zend_function_entry dse_graph_path_methods[] = {
  PHP_ME(DseGraphPath, __construct,  arginfo_none,    ZEND_ACC_PUBLIC | ZEND_ACC_CTOR)
  PHP_FE_END
};

static zend_object_handlers dse_graph_path_handlers;

static HashTable *
php_dse_graph_path_properties(zval *object TSRMLS_DC)
{
  HashTable *props = zend_std_get_properties(object TSRMLS_CC);

  return props;
}

static int
php_dse_graph_path_compare(zval *obj1, zval *obj2 TSRMLS_DC)
{
  if (Z_OBJCE_P(obj1) != Z_OBJCE_P(obj2))
    return 1; /* different classes */

  return Z_OBJ_HANDLE_P(obj1) != Z_OBJ_HANDLE_P(obj1);
}

static void
php_dse_graph_path_free(php5to7_zend_object_free *object TSRMLS_DC)
{
  dse_graph_path *self = PHP5TO7_ZEND_OBJECT_GET(dse_graph_path, object);

  /* Clean up */

  zend_object_std_dtor(&self->zval TSRMLS_CC);
  PHP5TO7_MAYBE_EFREE(self);
}

static php5to7_zend_object
php_dse_graph_path_new(zend_class_entry *ce TSRMLS_DC)
{
  dse_graph_path *self =
      PHP5TO7_ZEND_OBJECT_ECALLOC(dse_graph_path, ce);

  /* Initialize */

  PHP5TO7_ZEND_OBJECT_INIT(dse_graph_path, self, ce);
}

void cassandra_define_DseGraphPath(TSRMLS_D)
{
  zend_class_entry ce;

  INIT_CLASS_ENTRY(ce, "Dse\\Graph\\Path", dse_graph_path_methods);
  dse_graph_path_ce = zend_register_internal_class(&ce TSRMLS_CC);
  dse_graph_path_ce->ce_flags     |= PHP5TO7_ZEND_ACC_FINAL;
  dse_graph_path_ce->create_object = php_dse_graph_path_new;

  memcpy(&dse_graph_path_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));
  dse_graph_path_handlers.get_properties  = php_dse_graph_path_properties;
  dse_graph_path_handlers.compare_objects = php_dse_graph_path_compare;
  dse_graph_path_handlers.clone_obj = NULL;
}
