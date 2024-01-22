<p align="center">
    <picture>
        <img alt="WoollyM logo" width="40%" src="logos/woollym_logo.png">
    </picture>
</p>

> Documentation: __[WoollyM.dev](https://woollym.dev)__  
> Main Author: [Julien Boudry](https://www.linkedin.com/in/julienboudry/)  
> License: [BSD-3](LICENSE.txt) - _Please [say hello](https://github.com/MammothPHP/WoollyM/discussions/categories/your-projects-with-woolly) if you like or use this code!_  
> Donation: ₿ [bc1q3jllk3qd9fjvvuqy07tawkv7t6h7qjf55fc2gh](https://blockchair.com/bitcoin/address/bc1q3jllk3qd9fjvvuqy07tawkv7t6h7qjf55fc2gh) or [Github Sponsor Page](https://github.com/sponsors/julien-boudry)  
> _You can also offer me a bottle of [yellow wine from JURA](https://en.wikipedia.org/wiki/Vin_jaune)_  

> [!WARNING]
> This project is currently at an experimental stage. Production use is not recommended. APIs and functionalities are subject to change at any time without notice. Documentation is still deficient. Help and feedback are most welcome.

> [!TIP]
> The __[Official Documentation](https://woollym.dev)__ contains a complete presentation of the project, its features and the full API reference.  
> 
> <picture><img alt="WoollyM favicon" height="16px" style="vertical-align:middle" src="logos/woollym-favicon-color.png"></picture> __[WoollyM.dev](https://woollym.dev)__ <img alt="WoollyM favicon" height="16px" style="vertical-align:middle" src="logos/woollym-favicon-color.png"></picture>

---------------------
WoollyM is a PHP library for data analysis. It can be used to represent tabular data from various sources _(CSV, database, JSON, Excel...)_. The unified API can then be used easily to browse, analyze, modify, and export data in a variety of formats, we try to provide a very playful, modern, expressive, and user-friendly interface. This API is also modular and extensible, so you can easily add your own calculation and exploration methods.

Performances are optimized to be as light as possible on RAM during operations (input, output, read, write, stats, copy, clone), this is done using - internally - complex iterators and optimization preferring RAM over speed (even if we try to be fast also). The storage engine uses a modular storage system, if the default PhpArray driver uses RAM, the use of a database driver (such as the PDO driver) theoretically allows you to work on extremely large datasets. Using appropriate drivers, you can also write - for example - directly to the database (add, update) using the Woolly API.

---------------------
> [!NOTE]
> _Woolly was a fork from [archon/dataframe](https://github.com/hwperkins/Archon) project which was very useful and inspiring during development. Today, the internal engine has been almost completely rewritten and the public APIs are radically different and incompatible. A few traces of code and ideas remain, they have been placed by their original author under the BSD-3 license._