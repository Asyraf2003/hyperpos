# P1 - AWS Baseline

## Tujuan
Mengunci baseline infrastruktur AWS yang sudah dipilih project.

## Mandatory Rule
- Baseline aktif: CloudFront, S3, Lambda, SQS, DynamoDB.
- Provider non-AWS dianggap tidak aktif kecuali ada keputusan eksplisit.
- Jangan menggeser baseline provider secara diam-diam saat mengerjakan slice lain.

## Forbidden Behavior
- Jangan mengasumsikan multi-provider aktif tanpa keputusan eksplisit.
- Jangan memperlakukan provider alternatif sebagai baseline default.
