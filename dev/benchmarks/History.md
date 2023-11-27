

## Dev 2023-10-06

PHPBench (1.2.14) running benchmarks... #standwithukraine
with PHP version 8.2.10, xdebug ✔, opcache ✔

\IntensiveUsageBench

    benchSimpleStressTest1..................I4 - Mo780.554319ms (±22.90%)

Subjects: 1, Assertions: 0, Failures: 0, Errors: 0
+------+---------------------+------------------------+-----+------+--------------+-----------+--------------+----------------+
| iter | benchmark           | subject                | set | revs | mem_peak     | time_avg  | comp_z_value | comp_deviation |
+------+---------------------+------------------------+-----+------+--------------+-----------+--------------+----------------+
| 0    | IntensiveUsageBench | benchSimpleStressTest1 |     | 10   | 135,024,168b | 493.356ms | -1.14σ       | -26.05%        |
| 1    | IntensiveUsageBench | benchSimpleStressTest1 |     | 10   | 135,024,168b | 779.734ms | +0.74σ       | +16.88%        |
| 2    | IntensiveUsageBench | benchSimpleStressTest1 |     | 10   | 135,024,168b | 467.497ms | -1.31σ       | -29.92%        |
| 3    | IntensiveUsageBench | benchSimpleStressTest1 |     | 10   | 135,024,168b | 795.538ms | +0.84σ       | +19.25%        |
| 4    | IntensiveUsageBench | benchSimpleStressTest1 |     | 10   | 135,024,168b | 799.390ms | +0.87σ       | +19.83%        |
+------+---------------------+------------------------+-----+------+--------------+-----------+--------------+----------------+