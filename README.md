# igk/bcssParser
- parse bcss -> css 

balafon css (bcss) is a language to ease write css according to Balafon's requirement 

pour convertir un fichier 
```bash
balafon --bcss:parse nom_du_fichier
```

# directive in code @
```bcss
{
    display: inline-block;
    font-size: @font-size;
}
``` 
# propriété interne du code 

```bcss
// definition des directive du code 
# font-size 12
# --font-size 1.4em
div.card{   
    span{
        display: inline-block;
        font-size:  $--font-size   ;
    } 
}
```

**note** toutes les propriétés qui commense par '--' seront considérer comme des root 
```bcss
# font-size 12
# --font-size 1.4em
div.card{   
    span{
        --font-size: 2em;
        display: inline-block;
        font-size:  var(--font-size);
    } 
}
```
produira 

div.card span{--font-size:2em;display:inline-block;font-size:var(--font-size);}:root{--font-size:1.4em}


@C.A.D.BONDJEDOUE