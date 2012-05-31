<?php
/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2012  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'base/fs_model.php';
require_once 'model/agente.php';
require_once 'model/articulo.php';
require_once 'model/factura_proveedor.php';
require_once 'model/proveedor.php';

class linea_albaran_proveedor extends fs_model
{
   public $idlinea;
   public $idalbaran;
   public $referencia;
   public $descripcion;
   public $cantidad;
   public $dtopor;
   public $dtolineal;
   public $codimpuesto;
   public $iva;
   public $pvptotal;
   public $pvpsindto;
   public $pvpunitario;
   
   public function __construct($l=FALSE)
   {
      parent::__construct('lineasalbaranesprov');
      if($l)
      {
         $this->idlinea = intval($l['idlinea']);
         $this->idalbaran = intval($l['idalbaran']);
         $this->referencia = $l['referencia'];
         $this->descripcion = $l['descripcion'];
         $this->cantidad = floatval($l['cantidad']);
         $this->dtopor = floatval($l['dtopor']);
         $this->dtolineal = floatval($l['dtolineal']);
         $this->codimpuesto = $l['codimpuesto'];
         $this->iva = floatval($l['iva']);
         $this->pvptotal = floatval($l['pvptotal']);
         $this->pvpsindto = floatval($l['pvpsindto']);
         $this->pvpunitario = floatval($l['pvpunitario']);
      }
      else
      {
         $this->idlinea = NULL;
         $this->idalbaran = NULL;
         $this->referencia = '';
         $this->descripcion = '';
         $this->cantidad = 0;
         $this->dtopor = 0;
         $this->dtolineal = 0;
         $this->codimpuesto = NULL;
         $this->iva = 0;
         $this->pvptotal = 0;
         $this->pvpsindto = 0;
         $this->pvpunitario = 0;
      }
   }
   
   public function show_pvp()
   {
      return number_format($this->pvpunitario, 2, ',', '.');
   }
   
   public function show_total()
   {
      return number_format($this->pvptotal, 2, ',', '.');
   }
   
   public function url()
   {
      $alb = new albaran_proveedor();
      $alb = $alb->get($this->idalbaran);
      return $alb->url();
   }
   
   public function articulo_url()
   {
      $art = new articulo();
      $art = $art->get($this->referencia);
      return $art->url();
   }

   protected function install()
   {
      return '';
   }
   
   public function exists()
   {
      if( is_null($this->idlinea) )
         return false;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE idlinea = '".$this->idlinea."';");
   }
   
   public function new_idlinea()
   {
      $newid = $this->db->select("SELECT nextval('".$this->table_name."_idlinea_seq');");
      if($newid)
         $this->idlinea = intval($newid[0]['nextval']);
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET idalbaran = ".$this->var2str($this->idalbaran).",
            referencia = ".$this->var2str($this->referencia).", descripcion = ".$this->var2str($this->descripcion).",
            cantidad = ".$this->var2str($this->cantidad).", dtopor = ".$this->var2str($this->dtopor).",
            dtolineal = ".$this->var2str($this->dtolineal).", codimpuesto = ".$this->var2str($this->codimpuesto).",
            iva = ".$this->var2str($this->iva).", pvptotal = ".$this->var2str($this->pvptotal).",
            pvpsindto = ".$this->var2str($this->pvpsindto).", pvpunitario = ".$this->var2str($this->pvpunitario)."
            WHERE idlinea = '".$this->idlinea."';";
      }
      else
      {
         $this->new_idlinea();
         $sql = "INSERT INTO ".$this->table_name." (idlinea,idalbaran,referencia,descripcion,cantidad,dtopor,
            dtolineal,codimpuesto,iva,pvptotal,pvpsindto,pvpunitario) VALUES (".$this->var2str($this->idlinea).",".$this->var2str($this->idalbaran).",
            ".$this->var2str($this->referencia).",".$this->var2str($this->descripcion).",".$this->var2str($this->cantidad).",
            ".$this->var2str($this->dtopor).",".$this->var2str($this->dtolineal).",".$this->var2str($this->codimpuesto).",
            ".$this->var2str($this->iva).",".$this->var2str($this->pvptotal).",".$this->var2str($this->pvpsindto).",
            ".$this->var2str($this->pvpunitario).");";
      }
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE idlinea = '".$this->idlinea."';");
   }
   
   public function all_from_albaran($id)
   {
      $linealist = array();
      $lineas = $this->db->select("SELECT * FROM ".$this->table_name." WHERE idalbaran = '".$id."';");
      if($lineas)
      {
         foreach($lineas as $l)
            $linealist[] = new linea_albaran_proveedor($l);
      }
      return $linealist;
   }
   
   public function all_from_articulo($ref, $offset=0)
   {
      $linealist = array();
      $lineas = $this->db->select_limit("SELECT * FROM ".$this->table_name." WHERE referencia = '".$ref."' ORDER BY idalbaran DESC",
              FS_ITEM_LIMIT, $offset);
      if( $lineas )
      {
         foreach($lineas as $l)
            $linealist[] = new linea_albaran_proveedor($l);
      }
      return $linealist;
   }
}

class albaran_proveedor extends fs_model
{
   public $idalbaran;
   public $idfactura;
   public $codigo;
   public $numero;
   public $numproveedor;
   public $codejercicio;
   public $codserie;
   public $coddivisa;
   public $codpago;
   public $codagente;
   public $codalmacen;
   public $fecha;
   public $codproveedor;
   public $nombre;
   public $cifnif;
   public $neto;
   public $total;
   public $totaliva;
   public $totaleuros;
   public $irpf;
   public $totalirpf;
   public $tasaconv;
   public $recfinanciero;
   public $totalrecargo;
   public $observaciones;
   public $ptefactura;
   public $revisado;

   public function __construct($a=FALSE)
   {
      parent::__construct('albaranesprov');
      if($a)
      {
         $this->idalbaran = intval($a['idalbaran']);
         $this->idfactura = intval($a['idfactura']);
         $this->codigo = $a['codigo'];
         $this->numero = $a['numero'];
         $this->numproveedor = $a['numproveedor'];
         $this->codejercicio = $a['codejercicio'];
         $this->codserie = $a['codserie'];
         $this->coddivisa = $a['coddivisa'];
         $this->codpago = $a['codpago'];
         $this->codagente = $a['codagente'];
         $this->codalmacen = $a['codalmacen'];
         $this->fecha = $a['fecha'];
         $this->codproveedor = $a['codproveedor'];
         $this->nombre = $a['nombre'];
         $this->cifnif = $a['cifnif'];
         $this->neto = floatval($a['neto']);
         $this->total = floatval($a['total']);
         $this->totaliva = floatval($a['totaliva']);
         $this->totaleuros = floatval($a['totaleuros']);
         $this->irpf = floatval($a['irpf']);
         $this->totalirpf = floatval($a['totalirpf']);
         $this->tasaconv = floatval($a['tasaconv']);
         $this->recfinanciero = floatval($a['recfinanciero']);
         $this->totalrecargo = floatval($a['totalrecargo']);
         $this->observaciones = $a['observaciones'];
         $this->ptefactura = ($a['ptefactura'] == 't');
         $this->revisado = ($a['revisado'] == 't');
      }
      else
      {
         $this->idalbaran = NULL;
         $this->idfactura = NULL;
         $this->codigo = '';
         $this->numero = '';
         $this->numproveedor = '';
         $this->codejercicio = NULL;
         $this->codserie = NULL;
         $this->coddivisa = NULL;
         $this->codpago = NULL;
         $this->codagente = NULL;
         $this->codalmacen = NULL;
         $this->fecha = Date('d-m-Y');
         $this->codproveedor = NULL;
         $this->nombre = '';
         $this->cifnif = '';
         $this->neto = 0;
         $this->total = 0;
         $this->totaliva = 0;
         $this->totaleuros = 0;
         $this->irpf = 0;
         $this->totalirpf = 0;
         $this->tasaconv = 1;
         $this->recfinanciero = 0;
         $this->totalrecargo = 0;
         $this->observaciones = '';
         $this->ptefactura = TRUE;
         $this->revisado = FALSE;
      }
   }
   
   public function show_neto()
   {
      return number_format($this->neto, 2, ',', ' ');
   }
   
   public function show_iva()
   {
      return number_format($this->totaliva, 2, ',', ' ');
   }
   
   public function show_total()
   {
      return number_format($this->totaleuros, 2, ',', '.');
   }
   
   public function show_fecha()
   {
      return Date('d-m-Y', strtotime($this->fecha));
   }
   
   public function observaciones_resume()
   {
      if($this->observaciones == '')
         return '-';
      else if( strlen($this->observaciones) < 60 )
         return $this->observaciones;
      else
         return substr($this->observaciones, 0, 50).'...';
   }
   
   public function url()
   {
      return 'index.php?page=general_albaran_prov&id='.$this->idalbaran;
   }
   
   public function factura_url()
   {
      if( !$this->ptefactura )
      {
         $fac = new factura_proveedor();
         $fac = $fac->get($this->idfactura);
         if($fac)
            return $fac->url();
         else
            return $this->url();
      }
      else
         return $this->url();
   }
   
   public function agente_url()
   {
      $agente = new agente();
      $agente = $agente->get($this->codagente);
      return $agente->url();
   }
   
   public function proveedor_url()
   {
      $pro = new proveedor();
      $pro = $pro->get($this->codproveedor);
      return $pro->url();
   }
   
   public function get($id)
   {
      $albaran = $this->db->select("SELECT * FROM ".$this->table_name." WHERE idalbaran = '".$id."';");
      if($albaran)
         return new albaran_proveedor($albaran[0]);
      else
         return FALSE;
   }
   
   public function get_lineas()
   {
      $linea = new linea_albaran_proveedor();
      return $linea->all_from_albaran($this->idalbaran);
   }
   
   public function get_agente()
   {
      $agente = new agente();
      return $agente->get($this->codagente);
   }
   
   protected function install()
   {
      return '';
   }
   
   public function exists()
   {
      if( is_null($this->idalbaran) )
         return FALSE;
      else
         return $this->db->select("SELECT * FROM ".$this->table_name." WHERE idalbaran = '".$this->idalbaran."';");
   }
   
   public function new_idalbaran()
   {
      $newid = $this->db->select("SELECT nextval('".$this->table_name."_idalbaran_seq');");
      if($newid)
         $this->idalbaran = intval($newid[0]['nextval']);
   }
   
   public function new_codigo()
   {
      $numero = $this->db->select("SELECT MAX(numero::integer) as num FROM ".$this->table_name."
         WHERE codejercicio = ".$this->var2str($this->codejercicio)." AND codserie = ".$this->var2str($this->codserie).";");
      if($numero)
         $this->numero = sprintf('%06s', (1 + intval($numero[0]['num'])));
      else
         $this->numero = '000001';
      $this->codigo = $this->codejercicio . sprintf('%02s', $this->codserie) . sprintf('%06s', $this->numero);
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE ".$this->table_name." SET idfactura = ".$this->var2str($this->idfactura).",
            codigo = ".$this->var2str($this->codigo).", numero = ".$this->var2str($this->numero).",
            numproveedor = ".$this->var2str($this->numproveedor).", codejercicio = ".$this->var2str($this->codejercicio).",
            codserie = ".$this->var2str($this->codserie).", coddivisa = ".$this->var2str($this->coddivisa).",
            codpago = ".$this->var2str($this->codpago).", codagente = ".$this->var2str($this->codagente).",
            codalmacen = ".$this->var2str($this->codalmacen).", fecha = ".$this->var2str($this->fecha).",
            codproveedor = ".$this->var2str($this->codproveedor).", nombre = ".$this->var2str($this->nombre).",
            cifnif = ".$this->var2str($this->cifnif).", neto = ".$this->var2str($this->neto).",
            total = ".$this->var2str($this->total).", totaliva = ".$this->var2str($this->totaliva).",
            totaleuros = ".$this->var2str($this->totaleuros).", irpf = ".$this->var2str($this->irpf).",
            totalirpf = ".$this->var2str($this->totalirpf).", tasaconv = ".$this->var2str($this->tasaconv).",
            recfinanciero = ".$this->var2str($this->recfinanciero).", totalrecargo = ".$this->var2str($this->totalrecargo).",
            revisado = ".$this->var2str($this->revisado).", observaciones = ".$this->var2str($this->observaciones).",
            ptefactura = ".$this->var2str($this->ptefactura)." WHERE idalbaran = '".$this->idalbaran."';";
      }
      else
      {
         $this->new_idalbaran();
         $this->new_codigo();
         $sql = "INSERT INTO ".$this->table_name." (idalbaran,codigo,numero,numproveedor,codejercicio,codserie,coddivisa,
            codpago,codagente,codalmacen,fecha,codproveedor,nombre,cifnif,neto,total,totaliva,totaleuros,irpf,totalirpf,
            tasaconv,recfinanciero,totalrecargo,observaciones,ptefactura,revisado) VALUES ('".$this->idalbaran."',
            ".$this->var2str($this->codigo).",".$this->var2str($this->numero).",".$this->var2str($this->numproveedor).",
            ".$this->var2str($this->codejercicio).",".$this->var2str($this->codserie).",".$this->var2str($this->coddivisa).",
            ".$this->var2str($this->codpago).",".$this->var2str($this->codagente).",".$this->var2str($this->codalmacen).",
            ".$this->var2str($this->fecha).",".$this->var2str($this->codproveedor).",".$this->var2str($this->nombre).",
            ".$this->var2str($this->cifnif).",".$this->var2str($this->neto).",".$this->var2str($this->total).",
            ".$this->var2str($this->totaliva).",".$this->var2str($this->totaleuros).",".$this->var2str($this->irpf).",
            ".$this->var2str($this->totalirpf).",".$this->var2str($this->tasaconv).",".$this->var2str($this->recfinanciero).",
            ".$this->var2str($this->totalrecargo).",".$this->var2str($this->observaciones).",".$this->var2str($this->ptefactura).",
            ".$this->var2str($this->revisado).");";
      }
      return $this->db->exec($sql);
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM ".$this->table_name." WHERE idalbaran = '".$this->idalbaran."';");
   }
   
   public function all($offset=0)
   {
      $albalist = array();
      $albaranes = $this->db->select_limit("SELECT * FROM ".$this->table_name." ORDER BY fecha DESC",
                                           FS_ITEM_LIMIT, $offset);
      if($albaranes)
      {
         foreach($albaranes as $a)
            $albalist[] = new albaran_proveedor($a);
      }
      return $albalist;
   }
   
   public function all_from_proveedor($codproveedor, $offset=0)
   {
      $alblist = array();
      $albaranes = $this->db->select_limit("SELECT * FROM ".$this->table_name."  WHERE codproveedor = '".$codproveedor."' ORDER BY fecha DESC",
              FS_ITEM_LIMIT, $offset);
      if($albaranes)
      {
         foreach($albaranes as $a)
            $alblist[] = new albaran_proveedor($a);
      }
      return $alblist;
   }

   public function search($query, $offset=0)
   {
      $alblist = array();
      $query = strtolower($query);
      $albaranes = $this->db->select_limit("SELECT * FROM ".$this->table_name."  WHERE codigo ~~ '%".$query."%'
         OR lower(observaciones) ~~ '%".$query."%' ORDER BY fecha DESC", FS_ITEM_LIMIT, $offset);
      if($albaranes)
      {
         foreach($albaranes as $a)
            $alblist[] = new albaran_proveedor($a);
      }
      return $alblist;
   }
}

?>